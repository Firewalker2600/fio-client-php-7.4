<?php

namespace Firewalker\FioClient\Tests\Fixture;

final class FioApiFixture
{
    public static function minimalTransaction(int $id = 123, string $name = 'John Doe'): array
    {
        return [
            'accountStatement' => [
                'info' => self::minimalInfo(),
                'transactionList' => [
                    'transaction' => [
                        self::transaction($id, $name),
                    ],
                ],
            ],
        ];
    }

    public static function minimalInfo(): array
    {
        return [
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
        ];
    }

    public static function transaction(int $id, string $name): array
    {
        return [
            'column22' => ['value' => $id],
            'column0' => ['value' => '2024-01-01+0100'],
            'column1' => ['value' => 100.0],
            'column14' => ['value' => 'CZK'],
            'column10' => ['value' => $name],
        ];
    }

    public static function invalidJson(): string
    {
        return 'not-json';
    }
}