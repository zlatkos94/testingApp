<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClientService
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $url, $options);

        } catch (\Throwable $e) {
            throw new \RuntimeException('API request failed: ' . $e->getMessage());
        }
    }

}