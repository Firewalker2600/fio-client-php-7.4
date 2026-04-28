<?php

declare(strict_types=1);

namespace Firewalker\FioClient\Client;

use Firewalker\FioClient\Dto\AccountStatementDto;
use Firewalker\FioClient\Exception\FioJsonException;
use Firewalker\FioClient\Exception\HttpException;
use Psr\Http\Client\ClientExceptionInterface;

interface FioClientInterface
{
    /**
     * @throws ClientExceptionInterface
     */
    public function getTransactionsRaw(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        string             $format = FioFormat::JSON
    ): string;

    /**
     * @throws ClientExceptionInterface
     */
    public function getTransactionsDto(
        \DateTimeInterface $from,
        \DateTimeInterface $to
    ): AccountStatementDto;

    /**
     * @throws ClientExceptionInterface
     */
    public function getLastTransactionsRaw(
        string $format = FioFormat::JSON
    ): string;

    /**
     * @throws ClientExceptionInterface
     * @throws FioJsonException
     */
    public function getLastTransactionsDto(): AccountStatementDto;

    /**
     * @throws ClientExceptionInterface
     */
    public function getTransactionsSinceIdRaw(
        int    $id,
        string $format = FioFormat::JSON
    ): string;

    /**
     * @throws ClientExceptionInterface
     * @throws FioJsonException
     */
    public function getTransactionsSinceIdDto(int $id): AccountStatementDto;

    /**
     * @throws ClientExceptionInterface
     * @throws HttpException
     */
    public function setLastId(
        int    $id,
        string $format = FioFormat::JSON
    ): string;
}