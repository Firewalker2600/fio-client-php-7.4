<?php

namespace App\Service;

use Firewalker\FioClient\Dto\AccountStatementDto;

interface TransactionIdExtractorInterface
{
    /**
     * Extract latest transaction ID from DTO
     *
     * @throws \RuntimeException when ID cannot be determined
     */
    public function getLatestId(AccountStatementDto $dto): int;
}
