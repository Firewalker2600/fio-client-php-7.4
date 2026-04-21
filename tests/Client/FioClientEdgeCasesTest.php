<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\FioClient;
use App\Exception\HttpException;
use App\Exception\InvalidFormatException;
use App\Exception\JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FioClientEdgeCasesTest extends TestCase
{
    /**
     * Helper to create a FioClient with controlled HTTP response
     * @throws Exception
     */
    private function createClient(string $body = '', int $status = 200): FioClient
    {
        $request = $this->createStub(RequestInterface::class);

        $stream = $this->createStub(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('getBody')->willReturn($stream);

        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        return new FioClient('test-token', $httpClient, $requestFactory);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function testInvalidDateRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->createClient();
        $from = new \DateTimeImmutable('2024-01-02');
        $to = new \DateTimeImmutable('2024-01-01');

        // If your client validates this, it should throw
        $client->getTransactionsRaw($from, $to);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testEmptyAccountStatementJson(): void
    {
        $client = $this->createClient(json_encode(['accountStatement' => []]));
        $dto = $client->getTransactionsDTO(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-02')
        );

        $this->assertIsObject($dto);
        $this->assertSame([], $dto->transactions);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function testHttpErrorOnSetLastIdThrows(): void
    {
        $client = $this->createClient('error', 500);

        $this->expectException(HttpException::class);
        $client->setLastId(12345);
    }

    /**
     * @throws Exception
     */
    public function testClientExceptionPropagation(): void
    {
        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')
            ->willThrowException(new class extends \Exception implements ClientExceptionInterface {});

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($this->createStub(RequestInterface::class));

        $client = new FioClient('test-token', $httpClient, $requestFactory);

        $this->expectException(ClientExceptionInterface::class);
        $client->getLastTransactionsRaw();
    }

    /**
     * @throws Exception
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
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testInvalidJsonThrows(): void
    {
        $client = $this->createClient('not-json');

        $this->expectException(JsonException::class);
        $client->getTransactionsDTO(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-01-02')
        );
    }
}
