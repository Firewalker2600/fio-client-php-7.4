<?php

namespace App\Dto;

use App\Exception\FioJsonException;

class AccountInfoDto
{
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

    public static function fromArray(array $data): self
    {
        self::assertRequired($data, [
            'accountId',
            'bankId',
            'currency',
            'iban',
            'bic',
            'openingBalance',
            'closingBalance',
            'dateStart',
            'dateEnd',
        ]);

        $dto = new self();

        $dto->accountId = (string) $data['accountId'];
        $dto->bankId = (string) $data['bankId'];
        $dto->currency = (string) $data['currency'];
        $dto->iban = (string) $data['iban'];
        $dto->bic = (string) $data['bic'];

        $dto->openingBalance = (float) $data['openingBalance'];
        $dto->closingBalance = (float) $data['closingBalance'];

        try {
            $dto->dateStart = new \DateTimeImmutable($data['dateStart']);
        } catch (\Exception $e) {
            throw new FioJsonException("Invalid dateStart: {$data['dateStart']}");
        }

        try {
            $dto->dateEnd = new \DateTimeImmutable($data['dateEnd']);
        } catch (\Exception $e) {
            throw new FioJsonException("Invalid dateEnd: {$data['dateEnd']}");
        }

        $dto->yearList = $data['yearList'] ?? null;
        $dto->idList = $data['idList'] ?? null;

        $dto->idFrom = isset($data['idFrom']) ? (int) $data['idFrom'] : null;
        $dto->idTo = isset($data['idTo']) ? (int) $data['idTo'] : null;
        $dto->idLastDownload = isset($data['idLastDownload']) ? (int) $data['idLastDownload'] : null;

        return $dto;
    }

    private static function assertRequired(array $data, array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new FioJsonException("Missing required account info field: {$key}");
            }
        }
    }
}