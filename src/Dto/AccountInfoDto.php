<?php

namespace App\Dto;

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
        $dto = new self();
        $dto->accountId = $data['accountId'];
        $dto->bankId = $data['bankId'];
        $dto->currency = $data['currency'];
        $dto->iban = $data['iban'];
        $dto->bic = $data['bic'];
        $dto->openingBalance = (float) $data['openingBalance'];
        $dto->closingBalance = (float) $data['closingBalance'];
        $dto->dateStart = new \DateTimeImmutable($data['dateStart']);
        $dto->dateEnd = new \DateTimeImmutable($data['dateEnd']);
        $dto->yearList = $data['yearList'] ?? null;
        $dto->idList = $data['idList'] ?? null;
        $dto->idFrom = $data['idFrom'] ?? null;
        $dto->idTo = $data['idTo'] ?? null;
        $dto->idLastDownload = $data['idLastDownload'] ?? null;

        return $dto;
    }
}
