<?php

namespace App\Client;

use App\Dto\AccountStatementDto;

class FioResponse
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return raw array
     */
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * Convert to a DTO (currently supports account statements)
     */
    public function asDto(): AccountStatementDto
    {
        return AccountStatementDto::fromArray($this->data);
    }
}
