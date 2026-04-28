<?php

namespace Firewalker\FioClient\Service;

use Firewalker\FioClient\Dto\AccountStatementDto;

class DefaultTransactionIdExtractor implements TransactionIdExtractorInterface
{
    public function getLatestId(AccountStatementDto $dto): int
    {
        $transactions = $dto->transactions ?? [];

        if (empty($transactions)) {
            throw new \RuntimeException('No transactions available.');
        }

        $ids = [];

        foreach ($transactions as $transaction) {
            if (!isset($transaction->transactionId)) {
                continue;
            }

            $id = $transaction->transactionId;

            if (!is_numeric($id)) {
                continue;
            }

            $ids[] = (int) $id;
        }

        if (empty($ids)) {
            throw new \RuntimeException('No valid transaction IDs found.');
        }

        return max($ids);
    }
}
