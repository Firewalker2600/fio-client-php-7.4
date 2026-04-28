<?php

declare(strict_types=1);

namespace Firewalker\FioClient\Exception;

class HttpException extends FioException
{
    private int $statusCode;

    public function __construct(int $statusCode, string $message = '')
    {
        parent::__construct(
            $message ?: sprintf('FIO API request failed with status %d', $statusCode),
            $statusCode
        );

        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
