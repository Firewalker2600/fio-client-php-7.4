<?php

declare(strict_types=1);

namespace Firewalker\FioClient\Dto;

class TransactionDto
{
    public int $transactionId;
    public ?int $orderId;
    public \DateTimeImmutable $date;
    public float $amount;
    public string $currency;

    public ?string $counterpartyAccount;
    public ?string $counterpartyName;
    public ?string $counterpartyBankCode;
    public ?string $counterpartyBankName;
    public ?string $variableSymbol;
    public ?string $userIdentification;
    public ?string $message;
    public ?string $type;
    public ?string $performedBy;
    public ?string $comment;

    public function __construct(
        int $transactionId,
        ?int $orderId,
        \DateTimeImmutable $date,
        float $amount,
        string $currency,
        ?string $counterpartyAccount,
        ?string $counterpartyName,
        ?string $counterpartyBankCode,
        ?string $counterpartyBankName,
        ?string $variableSymbol,
        ?string $userIdentification,
        ?string $message,
        ?string $type,
        ?string $performedBy,
        ?string $comment
    ) {
        $this->transactionId = $transactionId;
        $this->orderId = $orderId;
        $this->date = $date;
        $this->amount = $amount;
        $this->currency = $currency;

        $this->counterpartyAccount = $counterpartyAccount;
        $this->counterpartyName = $counterpartyName;
        $this->counterpartyBankCode = $counterpartyBankCode;
        $this->counterpartyBankName = $counterpartyBankName;
        $this->variableSymbol = $variableSymbol;
        $this->userIdentification = $userIdentification;
        $this->message = $message;
        $this->type = $type;
        $this->performedBy = $performedBy;
        $this->comment = $comment;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            self::getInt($data, 'column22'),
            self::getNullableInt($data, 'column17'),
            self::getDate($data, 'column0'),
            self::getFloat($data, 'column1'),
            self::getString($data, 'column14'),

            self::getNullableString($data, 'column2'),
            self::getNullableString($data, 'column10'),
            self::getNullableString($data, 'column3'),
            self::getNullableString($data, 'column12'),
            self::getNullableString($data, 'column5'),
            self::getNullableString($data, 'column7'),
            self::getNullableString($data, 'column16'),
            self::getNullableString($data, 'column8'),
            self::getNullableString($data, 'column9'),
            self::getNullableString($data, 'column25'),
        );
    }

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

        $date = \DateTimeImmutable::createFromFormat('Y-m-dO', $value);

        if (!$date) {
            throw new \RuntimeException("Invalid date format in {$column}: {$value}");
        }

        return $date;
    }
}