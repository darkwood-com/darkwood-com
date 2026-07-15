<?php

declare(strict_types=1);

namespace App\Security\Sso;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @author Mathieu Ledru
 */
final readonly class SsoClientRegistry
{
    /** @var array<string, SsoClient> */
    private array $clientsById;

    /**
     * @param array<string, array{audience: string, redirect_uris: list<string>}> $clients
     */
    public function __construct(
        #[Autowire('%sso.clients%')]
        array $clients,
    ) {
        $indexed = [];
        foreach ($clients as $clientId => $config) {
            $indexed[$clientId] = new SsoClient(
                $clientId,
                $config['audience'],
                $config['redirect_uris'],
            );
        }

        $this->clientsById = $indexed;
    }

    public function get(string $clientId): ?SsoClient
    {
        return $this->clientsById[$clientId] ?? null;
    }
}
