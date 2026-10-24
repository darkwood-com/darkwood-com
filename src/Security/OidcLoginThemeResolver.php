<?php

declare(strict_types=1);

namespace App\Security;

use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

use function is_array;
use function is_string;
use function parse_str;
use function parse_url;

/**
 * Resolves a login/TOTP visual theme from a validated OIDC authorize client_id.
 *
 * Source of truth: Symfony `_security.main.target_path` (full /authorize URI saved
 * before login). Never trusts a `?theme=` query parameter.
 *
 * @author Mathieu Ledru
 */
final readonly class OidcLoginThemeResolver
{
    public const string DEFAULT_THEME = 'default';

    public const string SESSION_THEME_KEY = 'app.oidc.login_theme';

    private const string TARGET_PATH_KEY = '_security.main.target_path';

    /**
     * @param array<string, string> $themeMap client_id => theme id allowlist
     */
    public function __construct(
        private ClientManagerInterface $clientManager,
        private WebAuthenticationHostResolver $authenticationHostResolver,
        #[Autowire('%darkwood_host%')]
        private string $darkwoodHost,
        #[Autowire('%api_host%')]
        private string $apiHost,
        #[Autowire('%oidc_login_themes%')]
        private array $themeMap,
    ) {}

    public function resolve(Request $request): string
    {
        $session = $request->getSession();
        $clientId = $this->extractAuthorizedClientId($session->get(self::TARGET_PATH_KEY));

        if (null === $clientId) {
            return $this->fallbackOrDefault($request);
        }

        $client = $this->clientManager->find($clientId);
        if (null === $client || !$client->isActive()) {
            return $this->fallbackOrDefault($request);
        }

        $theme = $this->themeMap[$clientId] ?? self::DEFAULT_THEME;
        if (!is_string($theme) || '' === $theme || 1 !== preg_match('/\A[a-z0-9_-]+\z/', $theme)) {
            return $this->fallbackOrDefault($request);
        }

        $session->set(self::SESSION_THEME_KEY, $theme);

        return $theme;
    }

    private function fallbackOrDefault(Request $request): string
    {
        $session = $request->getSession();
        $path = $request->getPathInfo();
        $cached = $session->get(self::SESSION_THEME_KEY);

        // Keep themed TOTP challenge if authorize target is briefly unavailable.
        if (
            ('/2fa' === $path || '/2fa_check' === $path)
            && is_string($cached)
            && 1 === preg_match('/\A[a-z0-9_-]+\z/', $cached)
        ) {
            return $cached;
        }

        $session->remove(self::SESSION_THEME_KEY);

        return self::DEFAULT_THEME;
    }

    private function extractAuthorizedClientId(mixed $targetPath): ?string
    {
        if (!is_string($targetPath) || '' === $targetPath) {
            return null;
        }

        $parts = parse_url($targetPath);
        if (!is_array($parts)) {
            return null;
        }

        $path = $parts['path'] ?? '';
        if (!$this->authenticationHostResolver->isOidcAuthorizePath($path)) {
            return null;
        }

        if (isset($parts['host'])) {
            $host = strtolower((string) $parts['host']);
            if ($host !== strtolower($this->darkwoodHost) && $host !== strtolower($this->apiHost)) {
                return null;
            }
        }

        $query = [];
        if (isset($parts['query']) && '' !== $parts['query']) {
            parse_str($parts['query'], $query);
        }

        $clientId = $query['client_id'] ?? null;
        if (!is_string($clientId) || '' === $clientId) {
            return null;
        }

        // Reject values that cannot be OAuth client identifiers.
        if (!preg_match('/\A[A-Za-z0-9._~-]{1,128}\z/', $clientId)) {
            return null;
        }

        return $clientId;
    }
}
