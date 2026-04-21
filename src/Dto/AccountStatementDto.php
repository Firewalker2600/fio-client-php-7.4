<?php

namespace App\Dto;

use App\Exception\FioJsonException;

class AccountStatementDto
{
    public AccountInfoDto $info;

    /** @var TransactionDto[] */
    public array $transactions = [];

    public static function fromArray(array $statement): self
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

        $dto = new self();

        $dto->info = AccountInfoDto::fromArray($statement['info']);

        $transactions = $statement['transactionList']['transaction'];

        if (isset($transactions['column22'])) {
            $transactions = [$transactions];
        }

        $dto->transactions = array_map(
            fn($t) => TransactionDto::fromArray($t),
            $transactions
        );

        return $dto;
    }
}