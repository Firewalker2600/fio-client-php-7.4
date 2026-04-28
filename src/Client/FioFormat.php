<?php

namespace App\Client;

class FioFormat
{
    public const JSON = 'json';
    public const XML = 'xml';
    public const CSV = 'csv';

    private const ALL = [
        self::JSON,
        self::XML,
        self::CSV,
    ];

    public static function isValid(string $format): bool
    {
        return in_array($format, self::ALL, true);
    }
}