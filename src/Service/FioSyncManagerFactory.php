<?php

declare(strict_types=1);

namespace App\Service;
use Firewalker\FioClient\Client\FioClientInterface;

final class FioSyncManagerFactory
{
    public static function create(
        FioClientInterface $client,
        ?TransactionIdExtractorInterface $extractor = null
    ): FioSyncManager {
        return new FioSyncManager(
            $client,
            $extractor ?? new DefaultTransactionIdExtractor()
        );
    }
}

