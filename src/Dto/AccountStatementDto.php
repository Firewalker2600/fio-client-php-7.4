<?php

declare(strict_types=1);

namespace App\Dto;

use App\Exception\FioJsonException;

class AccountStatementDto
{
    /**
     * @var AccountInfoDto
     */
    public AccountInfoDto $info;

    /**
     * @var TransactionDto[]
     */
    public array $transactions;

    public function __construct(AccountInfoDto $info, array $transactions)
    {
        $this->info = $info;
        $this->transactions = $transactions;
    }

    public static function fromArray(array $statement): self
    {
        self::assertValidStructure($statement);

        $transactions = self::extractTransactions($statement['transactionList']);

        $mapped = array_map(
            static fn(array $t): TransactionDto => TransactionDto::fromArray($t),
            $transactions
        );

        return new self(
            AccountInfoDto::fromArray($statement['info']),
            $mapped
        );
    }

    private static function assertValidStructure(array $statement): void
    {
        if (!isset($statement['info']) || !is_array($statement['info'])) {
            throw new FioJsonException('Missing accountStatement.info');
        }

        if (
            !isset($statement['transactionList']) ||
            !is_array($statement['transactionList']) ||
            !array_key_exists('transaction', $statement['transactionList'])
        ) {
            throw new FioJsonException('Missing transactionList');
        }
    }

    private static function extractTransactions(array $transactionList): array
    {
        $transactions = $transactionList['transaction'];

        // normalize single transaction → list
        if (isset($transactions['column22'])) {
            $transactions = [$transactions];
        }

        return $transactions;
    }
}