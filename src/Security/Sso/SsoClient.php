<?php

declare(strict_types=1);

namespace App\Security\Sso;

/**
 * @author Mathieu Ledru
 */
final readonly class SsoClient
{
    /**
     * @param list<string> $redirectUris
     */
    public function __construct(
        public string $clientId,
        public string $audience,
        public array $redirectUris,
    ) {}

    public function allowsRedirectUri(string $redirectUri): bool
    {
        return in_array($redirectUri, $this->redirectUris, true);
    }
}
