<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\FioClient;
use App\Exception\HttpException;
use App\Exception\InvalidFormatException;
use App\Exception\FioJsonException;
use App\Tests\Fixture\FioApiFixture;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FioClientEdgeCasesTest extends TestCase
{
    private function createClient(
        string $body = '',
        int $status = 200,
        ?string $expectedUrl = null
    ): FioClient {
        $request = $this->createStub(RequestInterface::class);

        $stream = $this->createStub(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('getBody')->willReturn($stream);

        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);

        if ($expectedUrl !== null) {
            $requestFactory->expects($this->once())
                ->method('createRequest')
                ->with('GET', $expectedUrl)
                ->willReturn($request);
        } else {
            $requestFactory->method('createRequest')->willReturn($request);
        }

        return new FioClient('test-token', $httpClient, $requestFactory);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testInvalidDateRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->createClient();

        $client->getTransactionsRaw(
            new \DateTimeImmutable('2024-01-02'),
            new \DateTimeImmutable('2024-01-01')
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testEmptyAccountStatementJsonThrows(): void
    {
        $client = $this->createClient(
            json_encode(['accountStatement' => []])
        );

        $this->expectException(FioJsonException::class);

        $client->getTransactionsDto(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-02')
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testHttpErrorOnSetLastIdThrows(): void
    {
        $client = $this->createClient('error', 500);

        $this->expectException(HttpException::class);

        $client->setLastId(12345);
    }

    public function testClientExceptionPropagation(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')
            ->willThrowException(new class extends \Exception implements ClientExceptionInterface {});

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')
            ->willReturn($this->createStub(RequestInterface::class));

        $client = new FioClient('test-token', $httpClient, $requestFactory);

        $this->expectException(ClientExceptionInterface::class);

        $client->getLastTransactionsRaw();
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testInvalidFormatThrows(): void
    {
        $client = $this->createClient();

        $this->expectException(InvalidFormatException::class);

        $client->getTransactionsRaw(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-02'),
            'yaml'
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testInvalidJsonThrows(): void
    {
        $client = $this->createClient('not-json');

        $this->expectException(FioJsonException::class);

        $client->getTransactionsDto(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-02')
        );
    }
}