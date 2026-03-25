<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BaserowService
{
    private $httpClient;
    private $host;
    private $username;
    private $password;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire('%baserow_host%')]
        ?string $host,
        #[Autowire('%baserow_username%')]
        ?string $username,
        #[Autowire('%baserow_password%')]
        ?string $password
    ) {
        $this->httpClient = $httpClient;
        $this->host = $host ?? '';
        $this->username = $username ?? '';
        $this->password = $password ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->host !== '' && $this->username !== '' && $this->password !== '';
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getBaserowToken(): string
    {
        $response = $this->httpClient->request('POST', $this->host . '/api/user/token-auth/', [
            'json' => [
                'username' => $this->username,
                'password' => $this->password,
            ],
        ]);

        $loginData = $response->toArray();

        return $loginData['token'];
    }
}
