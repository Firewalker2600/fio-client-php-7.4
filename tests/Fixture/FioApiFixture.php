<?php

namespace App\Tests\Fixture;

class FioApiFixture
{
    public static function accountInfo(array $overrides = []): array
    {
        return array_merge([
            'accountId' => '123',
            'bankId' => '2010',
            'currency' => 'CZK',
            'iban' => 'CZ0000000000000000000000',
            'bic' => 'FIOBCZPPXXX',
            'openingBalance' => 0,
            'closingBalance' => 0,
            'dateStart' => '2024-01-01+0100',
            'dateEnd' => '2024-01-02+0100',
            'yearList' => null,
            'idList' => null,
            'idFrom' => null,
            'idTo' => null,
            'idLastDownload' => null,
        ], $overrides);
    }

    public static function transaction(array $overrides = []): array
    {
        return array_merge([
            'column22' => ['value' => 1],
            'column0' => ['value' => '2024-01-01+0100'],
            'column1' => ['value' => 100.0],
            'column14' => ['value' => 'CZK'],
            'column2' => ['value' => null],
            'column10' => ['value' => null],
            'column17' => ['value' => null],
        ], $overrides);
    }

    public static function statement(array $overrides = [], array $transactions = []): array
    {
        return array_merge([
            'accountStatement' => [
                'info' => self::accountInfo(),
                'transactionList' => [
                    'transaction' => $transactions ?: [
                        self::transaction(),
                    ],
                ],
            ],
        ], $overrides);
    }
}
