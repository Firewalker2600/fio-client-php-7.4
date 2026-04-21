<?php

namespace App\Client;

use App\Dto\AccountStatementDto;
use App\Exception\HttpException;
use App\Exception\InvalidFormatException;
use App\Exception\JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class FioClient
{
    private string $token;
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
        RequestFactoryInterface $requestFactory
    ) {
        $this->token = $token;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
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
        $path = sprintf(
            'periods/%s/%s/%s/transactions',
            $this->token,
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        return $this->request($path, $format);
    }

    /**
     * Get transactions as an AccountStatementDto
     *
     * @throws ClientExceptionInterface
     * @throws JsonException|\JsonException
     */
    public function getTransactionsDTO(
        \DateTimeInterface $from,
        \DateTimeInterface $to
    ): AccountStatementDto {
        $json = $this->getTransactionsRaw($from, $to, self::FORMAT_JSON);
        $data = $this->decodeJson($json);

        if (!isset($data['accountStatement'])) {
            throw new JsonException('Missing accountStatement in response');
        }

        return AccountStatementDto::fromArray($data['accountStatement']);
    }

    /**
     * Get last transactions raw
     *
     * @throws ClientExceptionInterface
     */
    public function getLastTransactionsRaw(string $format = self::FORMAT_JSON): string
    {
        $path = sprintf('last/%s/transactions', $this->token);
        return $this->request($path, $format);
    }

    /**
     * Get last transactions as AccountStatementDto
     *
     * @throws ClientExceptionInterface
     * @throws JsonException|\JsonException
     */
    public function getLastTransactionsDTO(): AccountStatementDto
    {
        $json = $this->getLastTransactionsRaw(self::FORMAT_JSON);
        $data = $this->decodeJson($json);

        if (!isset($data['accountStatement'])) {
            throw new JsonException('Missing accountStatement in response');
        }

        return AccountStatementDto::fromArray($data['accountStatement']);
    }

    /**
     * Get transactions since a specific transaction ID (raw)
     *
     * @throws ClientExceptionInterface
     */
    public function getTransactionsSinceIdRaw(int $id, string $format = self::FORMAT_JSON): string
    {
        $path = sprintf('by-id/%s/%d/transactions', $this->token, $id);
        return $this->request($path, $format);
    }

    /**
     * Get transactions since a specific transaction ID (DTO)
     *
     * @throws ClientExceptionInterface
     * @throws JsonException|\JsonException
     */
    public function getTransactionsSinceIdDTO(int $id): AccountStatementDto
    {
        $json = $this->getTransactionsSinceIdRaw($id, self::FORMAT_JSON);
        $data = $this->decodeJson($json);

        if (!isset($data['accountStatement'])) {
            throw new JsonException('Missing accountStatement in response');
        }

        return AccountStatementDto::fromArray($data['accountStatement']);
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
        return $this->request($path, $format);
    }

    private function buildUrl(string $path, string $format): string
    {
        return sprintf('%s/%s.%s', self::BASE_URL, $path, $format);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws InvalidFormatException
     */
    private function request(string $path, string $format): string
    {
        if (!in_array($format, self::ALLOWED_FORMATS, true)) {
            throw new InvalidFormatException('Invalid format');
        }

        $url = $this->buildUrl($path, $format);
        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() >= 300) {
            throw new HttpException($response->getStatusCode(), 'FIO API request failed');
        }

        return (string)$response->getBody();
    }

    /**
     * Decode JSON and throw on error
     *
     * @throws JsonException|\JsonException
     */
    private function decodeJson(string $content): array
    {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new JsonException('Invalid JSON from FIO API');
        }

        return $data;
    }
}
