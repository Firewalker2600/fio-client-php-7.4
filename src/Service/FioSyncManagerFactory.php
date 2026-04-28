<?php

declare(strict_types=1);

namespace Firewalker\FioClient\Service;
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

