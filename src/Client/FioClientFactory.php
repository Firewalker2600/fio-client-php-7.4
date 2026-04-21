<?php

namespace App\Client;

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class FioClientFactory
{
    public static function create(
        string $token,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null
    ): FioClient {
        $httpClient = $httpClient ?? self::createDefaultHttpClient();
        $requestFactory = $requestFactory ?? self::createDefaultRequestFactory();

        return new FioClient(
            $token,
            $httpClient,
            $requestFactory
        );
    }

    public static function fromEnv(): FioClient
    {
        $token = $_ENV['FIO_TOKEN'];

        if (!$token) {
            throw new \RuntimeException('FIO_TOKEN not set');
        }

        return self::create($token);
    }

    private static function createDefaultHttpClient(): ClientInterface
    {
        return new GuzzleClient([
            'timeout' => 10,
        ]);
    }

    private static function createDefaultRequestFactory(): RequestFactoryInterface
    {
        return new Psr17Factory();
    }
}
