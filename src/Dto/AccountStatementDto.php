<?php

namespace App\Dto;

class AccountStatementDto
{
    public AccountInfoDto $info;
    /** @var TransactionDto[] */
    public array $transactions;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->info = AccountInfoDto::fromArray($data['accountStatement']['info']);

        $transactionsData = $data['accountStatement']['transactionList']['transaction'];

        // Handle single transaction returned as an object
        if (isset($transactionsData['column22'])) {
            $transactionsData = [$transactionsData];
        }

        $dto->transactions = array_map(fn($t) => TransactionDto::fromArray($t), $transactionsData);

        return $dto;
    }
}
