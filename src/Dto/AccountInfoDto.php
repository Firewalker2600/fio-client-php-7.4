<?php

declare(strict_types=1);

namespace App\Dto;

use App\Exception\FioJsonException;

class AccountInfoDto
{
    private const REQUIRED_KEYS = [
        'accountId',
        'bankId',
        'currency',
        'iban',
        'bic',
        'openingBalance',
        'closingBalance',
        'dateStart',
        'dateEnd',
    ];

    public string $accountId;
    public string $bankId;
    public string $currency;
    public string $iban;
    public string $bic;
    public float $openingBalance;
    public float $closingBalance;
    public \DateTimeImmutable $dateStart;
    public \DateTimeImmutable $dateEnd;
    public ?array $yearList;
    public ?array $idList;
    public ?int $idFrom;
    public ?int $idTo;
    public ?int $idLastDownload;

    public function __construct(
        string $accountId,
        string $bankId,
        string $currency,
        string $iban,
        string $bic,
        float $openingBalance,
        float $closingBalance,
        \DateTimeImmutable $dateStart,
        \DateTimeImmutable $dateEnd,
        ?array $yearList = null,
        ?array $idList = null,
        ?int $idFrom = null,
        ?int $idTo = null,
        ?int $idLastDownload = null
    ) {
        $this->accountId = $accountId;
        $this->bankId = $bankId;
        $this->currency = $currency;
        $this->iban = $iban;
        $this->bic = $bic;

        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;

        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;

        $this->yearList = $yearList;
        $this->idList = $idList;
        $this->idFrom = $idFrom;
        $this->idTo = $idTo;
        $this->idLastDownload = $idLastDownload;
    }

    public static function fromArray(array $data): self
    {
        self::assertRequired($data);

        $dateStart = self::createDate($data['dateStart'], 'dateStart');
        $dateEnd = self::createDate($data['dateEnd'], 'dateEnd');

        return new self(
            (string) $data['accountId'],
            (string) $data['bankId'],
            (string) $data['currency'],
            (string) $data['iban'],
            (string) $data['bic'],
            (float) $data['openingBalance'],
            (float) $data['closingBalance'],
            $dateStart,
            $dateEnd,
            $data['yearList'] ?? null,
            $data['idList'] ?? null,
            isset($data['idFrom']) ? (int) $data['idFrom'] : null,
            isset($data['idTo']) ? (int) $data['idTo'] : null,
            isset($data['idLastDownload']) ? (int) $data['idLastDownload'] : null
        );
    }

    private static function assertRequired(array $data): void
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $data)) {
                throw new FioJsonException("Missing required account info field: {$key}");
            }
        }
    }

    private static function createDate(string $value, string $field): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $e) {
            throw new FioJsonException("Invalid {$field}: {$value}");
        }
    }
}