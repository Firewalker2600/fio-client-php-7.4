<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\FioClient;
use App\Dto\AccountStatementDto;
use App\Dto\TransactionDto;
use App\Exception\HttpException;
use App\Exception\InvalidFormatException;
use App\Exception\JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FioClientTest extends TestCase
{
    /**
     * @throws Exception
     */
    private function createClient(
        string $responseBody,
        string $expectedUrl,
        int $statusCode = 200,
        bool $expectRequest = true
    ): FioClient {

        /** @var RequestInterface&Stub $request */
        $request = $this->createStub(RequestInterface::class);

        /** @var StreamInterface&Stub $stream */
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('__toString')->willReturn($responseBody);

        /** @var ResponseInterface&Stub $response */
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($stream);

        /** @var ClientInterface&Stub $httpClient */
        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        /** @var RequestFactoryInterface&Stub $requestFactory */
        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        if ($expectRequest) {
            $requestFactory = $this->createMock(RequestFactoryInterface::class);
            $requestFactory
                ->expects($this->once())
                ->method('createRequest')
                ->with('GET', $expectedUrl)
                ->willReturn($request);
        }

        return new FioClient('test-token', $httpClient, $requestFactory);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function testGetTransactionsReturnsParsedJson(): void
    {
        $from = new \DateTimeImmutable('2024-01-01');
        $to = new \DateTimeImmutable('2024-01-02');

        $expectedUrl = sprintf(
            'https://fioapi.fio.cz/v1/rest/periods/test-token/%s/%s/transactions.json',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        $mockResponse = [
            'accountStatement' => [
                'info' => [
                    'accountId' => '123',
                    'bankId' => '456',
                    'currency' => 'CZK',
                    'iban' => 'CZ6508000000001234567899',
                    'bic' => 'GIBACZPX',
                    'openingBalance' => 1000.0,
                    'closingBalance' => 1500.0,
                    'dateStart' => '2024-01-01',
                    'dateEnd' => '2024-01-02',
                ],
                'transactionList' => [
                    'transaction' => [
                        'column22' => ['value' => 123],
                        'column17' => ['value' => 200],
                        'column0' => ['value' => '2024-01-02'],
                        'column1' => ['value' => 750.0],
                        'column14' => ['value' => 'CZK'],
                        'column2' => ['value' => '1234567890'],
                        'column10' => ['value' => 'John Doe'],
                    ],
                ],
            ],
        ];

        $client = $this->createClient(json_encode($mockResponse), $expectedUrl);

        $result = $client->getTransactions($from, $to);

        $this->assertInstanceOf(AccountStatementDto::class, $result);
        $this->assertCount(1, $result->transactions);
        $this->assertInstanceOf(TransactionDto::class, $result->transactions[0]);
        $this->assertSame(123, $result->transactions[0]->transactionId);
        $this->assertSame('John Doe', $result->transactions[0]->counterpartyName);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function testGetLastTransactionsReturnsParsedJson(): void
    {
        $expectedUrl = 'https://fioapi.fio.cz/v1/rest/last/test-token/transactions.json';

        $mockResponse = [
            'accountStatement' => [
                'info' => [],
                'transactionList' => [
                    'transaction' => [
                        'column22' => ['value' => 1],
                    ],
                ],
            ],
        ];

        $client = $this->createClient(json_encode($mockResponse), $expectedUrl);

        $result = $client->getLastTransactions();

        $this->assertInstanceOf(AccountStatementDto::class, $result);
        $this->assertSame(1, $result->transactions[0]->transactionId);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function testGetTransactionsSinceIdReturnsParsedJson(): void
    {
        $expectedUrl = 'https://fioapi.fio.cz/v1/rest/by-id/test-token/123/transactions.json';

        $mockResponse = [
            'accountStatement' => [
                'info' => [],
                'transactionList' => [
                    'transaction' => [
                        'column22' => ['value' => 123],
                        'column10' => ['value' => 'John Doe'],
                    ],
                ],
            ],
        ];

        $client = $this->createClient(json_encode($mockResponse), $expectedUrl);

        $result = $client->getTransactionsSinceId(123);

        $this->assertInstanceOf(AccountStatementDto::class, $result);
        $this->assertCount(1, $result->transactions);
        $this->assertSame(123, $result->transactions[0]->transactionId);
        $this->assertSame('John Doe', $result->transactions[0]->counterpartyName);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function testGetTransactionsRawReturnsString(): void
    {
        $from = new \DateTimeImmutable('2024-01-01');
        $to = new \DateTimeImmutable('2024-01-02');
        $expectedUrl = sprintf(
            'https://fioapi.fio.cz/v1/rest/periods/test-token/%s/%s/transactions.json',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        $client = $this->createClient('raw-data', $expectedUrl);

        $result = $client->getTransactionsRaw($from, $to);

        $this->assertSame('raw-data', $result);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function testXmlFormatIsUsed(): void
    {
        $from = new \DateTimeImmutable('2024-01-01');
        $to = new \DateTimeImmutable('2024-01-02');
        $expectedUrl = sprintf(
            'https://fioapi.fio.cz/v1/rest/periods/test-token/%s/%s/transactions.xml',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        $client = $this->createClient('<xml></xml>', $expectedUrl, 200, true);

        $result = $client->getTransactionsRaw($from, $to, FioClient::FORMAT_XML);

        $this->assertSame('<xml></xml>', $result);
    }

    /**
     * @throws ClientExceptionInterface|Exception
     */
    public function testInvalidFormatThrows(): void
    {
        $this->expectException(InvalidFormatException::class);

        $client = $this->createClient('', '', 200, false);

        $client->getTransactionsRaw(new \DateTimeImmutable(), new \DateTimeImmutable(), 'yaml');
    }

    /**
     * @throws ClientExceptionInterface|Exception
     */
    public function testHttpErrorThrows(): void
    {
        $this->expectException(HttpException::class);

        $expectedUrl = 'https://fioapi.fio.cz/v1/rest/last/test-token/transactions.json';
        $client = $this->createClient('error', $expectedUrl, 500);

        $client->getLastTransactions();
    }

    /**
     * @throws ClientExceptionInterface|Exception
     */
    public function testInvalidJsonThrows(): void
    {
        $this->expectException(JsonException::class);

        $expectedUrl = 'https://fioapi.fio.cz/v1/rest/last/test-token/transactions.json';
        $client = $this->createClient('not-json', $expectedUrl);

        $client->getLastTransactions();
    }
}
