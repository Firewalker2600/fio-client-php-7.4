<?php
namespace App\Dto;

class TransactionDto
{
    public int $transactionId;
    public ?int $orderId = null;
    public \DateTimeImmutable $date;
    public float $amount;
    public string $currency;

    public ?string $counterpartyAccount = null;
    public ?string $counterpartyName = null;
    public ?string $counterpartyBankCode = null;
    public ?string $counterpartyBankName = null;
    public ?string $variableSymbol = null;
    public ?string $userIdentification = null;
    public ?string $message = null;
    public ?string $type = null;
    public ?string $performedBy = null;
    public ?string $comment = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();

        // Required fields
        $dto->transactionId = self::getInt($data, 'column22');
        $dto->date = self::getDate($data, 'column0');
        $dto->amount = self::getFloat($data, 'column1');
        $dto->currency = self::getString($data, 'column14');

        // Optional fields
        $dto->orderId = self::getNullableInt($data, 'column17');
        $dto->counterpartyAccount = self::getNullableString($data, 'column2');
        $dto->counterpartyName = self::getNullableString($data, 'column10');
        $dto->counterpartyBankCode = self::getNullableString($data, 'column3');
        $dto->counterpartyBankName = self::getNullableString($data, 'column12');
        $dto->variableSymbol = self::getNullableString($data, 'column5');
        $dto->userIdentification = self::getNullableString($data, 'column7');
        $dto->message = self::getNullableString($data, 'column16');
        $dto->type = self::getNullableString($data, 'column8');
        $dto->performedBy = self::getNullableString($data, 'column9');
        $dto->comment = self::getNullableString($data, 'column25');

        return $dto;
    }

    // -------------------------
    // Helpers
    // -------------------------

    private static function getValue(array $data, string $column)
    {
        if (!isset($data[$column]) || !is_array($data[$column])) {
            return null;
        }

        return $data[$column]['value'] ?? null;
    }

    private static function getInt(array $data, string $column): int
    {
        $value = self::getValue($data, $column);

        if (!is_numeric($value)) {
            throw new \RuntimeException("Invalid or missing int in {$column}");
        }

        return (int) $value;
    }

    private static function getNullableInt(array $data, string $column): ?int
    {
        $value = self::getValue($data, $column);

        return is_numeric($value) ? (int) $value : null;
    }

    private static function getFloat(array $data, string $column): float
    {
        $value = self::getValue($data, $column);

        if (!is_numeric($value)) {
            throw new \RuntimeException("Invalid or missing float in {$column}");
        }

        return (float) $value;
    }

    private static function getString(array $data, string $column): string
    {
        $value = self::getValue($data, $column);

        if ($value === null) {
            throw new \RuntimeException("Missing string in {$column}");
        }

        return (string) $value;
    }

    private static function getNullableString(array $data, string $column): ?string
    {
        $value = self::getValue($data, $column);

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function getDate(array $data, string $column): \DateTimeImmutable
    {
        $value = self::getValue($data, $column);

        if (!$value) {
            throw new \RuntimeException("Missing date in {$column}");
        }

        // FIO format: 2026-03-01+0100
        $date = \DateTimeImmutable::createFromFormat('Y-m-dO', $value);

        if (!$date) {
            throw new \RuntimeException("Invalid date format in {$column}: {$value}");
        }

        return $date;
    }
}
