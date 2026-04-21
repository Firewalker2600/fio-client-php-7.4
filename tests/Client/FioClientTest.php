<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\FioClient;
use App\Dto\AccountStatementDto;
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

class FioClientTest extends TestCase
{
    private const BASE_URL = 'https://fioapi.fio.cz/v1/rest';
    private const TOKEN = 'test-token';

    private function url(string $path): string
    {
        return sprintf('%s/%s', self::BASE_URL, ltrim($path, '/'));
    }

    private function createClient(
        string $responseBody,
        string $expectedUrl,
        int $statusCode = 200,
        bool $expectRequest = true
    ): FioClient {
        $request = $this->createStub(RequestInterface::class);

        $stream = $this->createStub(StreamInterface::class);
        $stream->method('__toString')->willReturn($responseBody);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($stream);

        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);

        if ($expectRequest) {
            $requestFactory->expects($this->once())
                ->method('createRequest')
                ->willReturnCallback(function ($method, $url) use ($expectedUrl, $request) {
                    $this->assertSame('GET', $method);
                    $this->assertSame($expectedUrl, $url);
                    return $request;
                });
        }

        return new FioClient(self::TOKEN, $httpClient, $requestFactory);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testGetTransactionsReturnsParsedJson(): void
    {
        $from = new \DateTimeImmutable('2024-01-01');
        $to = new \DateTimeImmutable('2024-01-02');

        $expectedUrl = $this->url(sprintf(
            'periods/%s/%s/%s/transactions.json',
            self::TOKEN,
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        ));

        $payload = FioApiFixture::minimalTransaction(123, 'John Doe');

        $client = $this->createClient(json_encode($payload), $expectedUrl);

        $result = $client->getTransactionsDto($from, $to);

        $this->assertInstanceOf(AccountStatementDto::class, $result);
        $this->assertCount(1, $result->transactions);
        $this->assertSame(123, $result->transactions[0]->transactionId);
        $this->assertSame('John Doe', $result->transactions[0]->counterpartyName);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testGetLastTransactionsReturnsParsedJson(): void
    {
        $expectedUrl = $this->url('last/' . self::TOKEN . '/transactions.json');

        $payload = FioApiFixture::minimalTransaction(1);

        $client = $this->createClient(json_encode($payload), $expectedUrl);

        $result = $client->getLastTransactionsDto();

        $this->assertSame(1, $result->transactions[0]->transactionId);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testGetTransactionsSinceIdReturnsParsedJson(): void
    {
        $expectedUrl = $this->url('by-id/' . self::TOKEN . '/123/transactions.json');

        $payload = FioApiFixture::minimalTransaction(123, 'John Doe');

        $client = $this->createClient(json_encode($payload), $expectedUrl);

        $result = $client->getTransactionsSinceIdDto(123);

        $this->assertSame(123, $result->transactions[0]->transactionId);
        $this->assertSame('John Doe', $result->transactions[0]->counterpartyName);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testInvalidJsonThrows(): void
    {
        $this->expectException(FioJsonException::class);

        $expectedUrl = $this->url('last/' . self::TOKEN . '/transactions.json');

        $client = $this->createClient(
            FioApiFixture::invalidJson(),
            $expectedUrl
        );

        $client->getLastTransactionsDto();
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testHttpErrorThrows(): void
    {
        $this->expectException(HttpException::class);

        $expectedUrl = $this->url('last/' . self::TOKEN . '/transactions.json');

        $client = $this->createClient(
            json_encode(FioApiFixture::minimalTransaction()),
            $expectedUrl,
            500
        );

        $client->getLastTransactionsDto();
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function testInvalidFormatThrows(): void
    {
        $this->expectException(InvalidFormatException::class);

        $client = $this->createClient('', '', 200, false);

        $client->getTransactionsRaw(
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            'yaml'
        );
    }
}