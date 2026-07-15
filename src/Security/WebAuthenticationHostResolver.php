<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function in_array;
use function sprintf;

/**
 * Authentication UI (login, 2FA) only exists on the main Darkwood site host.
 * API host requests must be mirrored there while preserving the shared session cookie.
 *
 * @author Mathieu Ledru
 */
final readonly class WebAuthenticationHostResolver
{
    private const string SSO_AUTHORIZE_PATH = '/sso/authorize';

    private const array AUTH_PATHS = [
        '/login',
        '/logout',
        '/2fa',
        '/2fa_check',
        '/2fa/setup',
        '/fr/login',
        '/de/login',
        '/fr/logout',
        '/de/logout',
    ];

    public function __construct(
        #[Autowire('%api_host%')]
        private string $apiHost,
        #[Autowire('%darkwood_host%')]
        private string $darkwoodHost,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function isApiHost(string $host): bool
    {
        return $host === $this->apiHost;
    }

    public function authenticationHostFor(string $requestHost): string
    {
        return $this->isApiHost($requestHost) ? $this->darkwoodHost : $requestHost;
    }

    public function isAuthenticationPath(string $pathInfo): bool
    {
        return in_array($pathInfo, self::AUTH_PATHS, true);
    }

    public function isSsoAuthorizePath(string $pathInfo): bool
    {
        return self::SSO_AUTHORIZE_PATH === $pathInfo;
    }

    public function shouldMirrorToAuthenticationHost(Request $request): bool
    {
        if (!$this->isApiHost($request->getHost())) {
            return false;
        }

        $pathInfo = $request->getPathInfo();

        return $this->isAuthenticationPath($pathInfo) || $this->isSsoAuthorizePath($pathInfo);
    }

    public function mirrorUrl(Request $request): string
    {
        return $this->mirrorAuthenticationUri($request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri());
    }

    public function mirrorAuthenticationUri(string $uri): string
    {
        $parts = parse_url($uri);
        if (!is_array($parts) || !isset($parts['host']) || !$this->isApiHost($parts['host'])) {
            return $uri;
        }

        $path = $parts['path'] ?? '';
        if (!$this->isAuthenticationPath($path) && !$this->isSsoAuthorizePath($path)) {
            return $uri;
        }

        $parts['host'] = $this->darkwoodHost;

        return $this->buildUri($parts);
    }

    /**
     * @param array{scheme?: string, host?: string, port?: int, path?: string, query?: string, fragment?: string} $parts
     */
    private function buildUri(array $parts): string
    {
        $uri = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? $this->darkwoodHost);

        if (isset($parts['port'])) {
            $uri .= ':' . $parts['port'];
        }

        $uri .= $parts['path'] ?? '';

        if (isset($parts['query']) && '' !== $parts['query']) {
            $uri .= '?' . $parts['query'];
        }

        if (isset($parts['fragment']) && '' !== $parts['fragment']) {
            $uri .= '#' . $parts['fragment'];
        }

        return $uri;
    }

    public function loginUrl(Request $request): string
    {
        if (!$this->isApiHost($request->getHost())) {
            return $this->urlGenerator->generate(LoginFormAuthenticator::LOGIN_ROUTE);
        }

        $context = $this->urlGenerator->getContext();
        $previousHost = $context->getHost();
        $context->setHost($this->darkwoodHost);

        try {
            return $this->urlGenerator->generate(
                LoginFormAuthenticator::LOGIN_ROUTE,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        } finally {
            $context->setHost($previousHost);
        }
    }
}
