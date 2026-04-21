<?php

namespace App\Client;

use App\Dto\AccountStatementDto;
use App\Exception\FioDuplicateRequestException;
use App\Exception\HttpException;
use App\Exception\InvalidFormatException;
use App\Exception\FioJsonException;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class FioClient
{
    private string $token;
    private string $baseUrl;
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private const BASE_URL = 'https://fioapi.fio.cz/v1/rest';
    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';
    public const FORMAT_CSV = 'csv';
    private const ALLOWED_FORMATS = [
        self::FORMAT_JSON,
        self::FORMAT_XML,
        self::FORMAT_CSV
    ];

    public function __construct(
        string $token,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $baseUrl = self::BASE_URL
    ) {
        $this->token = $token;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get raw transactions between two dates.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param string $format
     * @return string
     * @throws ClientExceptionInterface
     */
    public function getTransactionsRaw(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        string $format = self::FORMAT_JSON
    ): string {
        if ($from > $to) {
            throw new InvalidArgumentException();
        }
        $path = sprintf(
            'periods/%s/%s/%s/transactions',
            $this->token,
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        return $this->request('GET', $path, $format);
    }

    /**
     * Get transactions as an AccountStatementDto
     *
     * @throws ClientExceptionInterface
     */
    public function getTransactionsDto(
        \DateTimeInterface $from,
        \DateTimeInterface $to
    ): AccountStatementDto {
        $json = $this->getTransactionsRaw($from, $to, self::FORMAT_JSON);

        return $this->mapToAccountStatement($json);
    }

    /**
     * Get last transactions raw
     *
     * @throws ClientExceptionInterface
     */
    public function getLastTransactionsRaw(string $format = self::FORMAT_JSON): string
    {
        $path = sprintf('last/%s/transactions', $this->token);
        return $this->request('GET', $path, $format);
    }

    /**
     * Get last transactions as AccountStatementDto
     *
     * @throws ClientExceptionInterface
     * @throws FioJsonException
     */
    public function getLastTransactionsDto(): AccountStatementDto
    {
        $json = $this->getLastTransactionsRaw(self::FORMAT_JSON);

        return $this->mapToAccountStatement($json);
    }

    /**
     * Get transactions since a specific transaction ID (raw)
     *
     * @throws ClientExceptionInterface
     */
    public function getTransactionsSinceIdRaw(int $id, string $format = self::FORMAT_JSON): string
    {
        $path = sprintf('by-id/%s/%d/transactions', $this->token, $id);

        return $this->request('GET', $path, $format);
    }

    /**
     * Get transactions since a specific transaction ID (DTO)
     *
     * @throws ClientExceptionInterface
     * @throws FioJsonException
     */
    public function getTransactionsSinceIdDto(int $id): AccountStatementDto
    {
        $json = $this->getTransactionsSinceIdRaw($id, self::FORMAT_JSON);

        return $this->mapToAccountStatement($json);
    }

    /**
     * Set the marker for the last downloaded transaction.
     *
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws InvalidFormatException
     */
    public function setLastId(int $id, string $format = self::FORMAT_JSON): string
    {
        $path = sprintf('set-last-id/%s/%d', $this->token, $id);

        return $this->request('GET', $path, $format);
    }

    private function buildUrl(string $path, string $format): string
    {
        return sprintf('%s/%s.%s', $this->baseUrl, $path, $format);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws InvalidFormatException
     */
    private function request(string $method, string $path, string $format): string
    {
        if (!in_array($format, self::ALLOWED_FORMATS, true)) {
            throw new InvalidFormatException('Invalid format');
        }

        $url = $this->buildUrl($path, $format);
        $request = $this->requestFactory->createRequest($method, $url);
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() === 409) {
            throw new FioDuplicateRequestException(
                $response->getStatusCode(),
                'FIO API rejected a duplicate request (requests repeated within a short time window may be rejected, typically ~30s).'
            );
        }

        if ($response->getStatusCode() >= 300) {
            throw new HttpException(
                $response->getStatusCode(),
                sprintf('FIO API request failed: %s', $response->getBody())
            );
        }

        return (string)$response->getBody();
    }

    /**
     * Decode JSON and throw on error
     *
     * @throws FioJsonException|JsonException
     */
    private function decodeJson(string $content): array
    {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new FioJsonException('Invalid JSON from FIO API');
        }

        return $data;
    }

    /**
     * @param string $json
     * @return AccountStatementDto
     */
    private function mapToAccountStatement(string $json): AccountStatementDto
    {
        try {
            $data = $this->decodeJson($json);
        } catch (JsonException $e) {
            throw new FioJsonException('Invalid JSON from FIO API');
        }

        if (!isset($data['accountStatement'])) {
            throw new FioJsonException('Missing accountStatement in response');
        }

        return AccountStatementDto::fromArray($data['accountStatement']);
    }
}
