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

    public function shouldMirrorToAuthenticationHost(Request $request): bool
    {
        return $this->isApiHost($request->getHost())
            && $this->isAuthenticationPath($request->getPathInfo());
    }

    public function mirrorUrl(Request $request): string
    {
        return sprintf(
            '%s://%s%s',
            $request->getScheme(),
            $this->authenticationHostFor($request->getHost()),
            $request->getRequestUri(),
        );
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
