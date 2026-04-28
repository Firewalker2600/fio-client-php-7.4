<?php

namespace App\Service;

use App\Client\FioClientInterface;
use App\Dto\AccountStatementDto;
use Psr\Http\Client\ClientExceptionInterface;

final class FioSyncManager
{
    private FioClientInterface $client;
    private TransactionIdExtractorInterface $extractor;

    public function __construct(FioClientInterface $client, TransactionIdExtractorInterface $extractor)
    {
        $this->client = $client;
        $this->extractor = $extractor;
    }

    /**
     * Initialize the FIO "last transaction" marker.
     *
     * This MUST be called before using incremental sync (getLastTransactions),
     * otherwise the API will force 2-factor authentication before responding with older data or fail (90-day limitation).
     *
     * @param int $days How far back to look (max 90)
     * @return int The last transaction ID that was set
     * @throws ClientExceptionInterface
     */
    public function initialize(int $days = 30): int
    {
        $days = min($days, 90);

        $dto = $this->client->getTransactionsDto(
            new \DateTimeImmutable("-{$days} days"),
            new \DateTimeImmutable()
        );

        return $this->initializeFromDto($dto);
    }

    /**
     * Initialize using already fetched DTO (useful for testing or batching).
     * @throws ClientExceptionInterface
     */
    public function initializeFromDto(AccountStatementDto $dto): int
    {
        $id = $this->extractor->getLatestId($dto);

        $this->client->setLastId($id);

        return $id;
    }

    /**
     * Fetch new transactions since last known marker.
     *
     * @return AccountStatementDto
     * @throws ClientExceptionInterface
     */
    public function fetchNew(): AccountStatementDto
    {
        return $this->client->getLastTransactionsDto();
    }

    /**
     * Full safe sync:
     * - initializes if needed (optional)
     * - fetches new transactions
     *
     * @param bool $initializeIfNeeded
     * @param int $days
     * @return AccountStatementDto
     * @throws ClientExceptionInterface
     */
    public function sync(bool $initializeIfNeeded = false, int $days = 30): AccountStatementDto
    {
        if ($initializeIfNeeded) {
            $this->initialize($days);
        }

        return $this->fetchNew();
    }
}
