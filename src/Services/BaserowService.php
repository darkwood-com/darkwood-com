<?php

declare(strict_types=1);

namespace App\Services;

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
        #[Autowire('%env(BASEROW_HOST)%')]
        string $host,
        #[Autowire('%env(BASEROW_USERNAME)%')]
        string $username,
        #[Autowire('%env(BASEROW_PASSWORD)%')]
        string $password
    ) {
        $this->httpClient = $httpClient;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getBaserowToken(): string
    {
        // Get Baserow token
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
