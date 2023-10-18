<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\UserRepository;
use App\Services\SiteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use \Symfony\Component\Security\Http\Util\TargetPathTrait;
    public const LOGIN_ROUTE = 'security_login';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private SiteService $siteService,
        private ParameterBagInterface $parameterBag,
        private UserRepository $userRepository,
        private UserProviderInterface $userProvider
    ) {
    }

    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);

        $passport = new Passport(
            new UserBadge($credentials['username'], function (string $identifier): UserInterface {
                return $this->userProvider->loadUserByIdentifier($identifier);
            }),
            new PasswordCredentials($credentials['password']),
            [new RememberMeBadge()]
        );
        /*if ($this->options['enable_csrf']) {
            $passport->addBadge(new CsrfTokenBadge($this->options['csrf_token_id'], $credentials['csrf_token']));
        }*/

        if ($this->userProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->userProvider));
        }

        return $passport;
    }

    public function getCredentials(Request $request)
    {
        $credentials = ['username' => $request->request->get('username'), 'password' => $request->request->get('password'), 'csrf_token' => $request->request->get('_csrf_token')];
        $request->getSession()->set(\Symfony\Component\Security\Http\SecurityRequestAttributes::LAST_USERNAME, $credentials['username']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new \Symfony\Component\Security\Csrf\CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->userRepository->loadUserByIdentifier($credentials['username']);
        if (!$user instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        $redirectUrl = $request->headers->get('Referer');
        if (str_contains($redirectUrl, 'login')) {
            $redirectUrl = $this->urlGenerator->generate('darkwood_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $host = $request->getHost();
            $site = $this->siteService->findOneByHost($host);
            if ($host === $this->parameterBag->get('admin_host')) {
                $redirectUrl = $this->urlGenerator->generate('admin_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            } elseif ($site !== null) {
                $redirectUrl = $this->urlGenerator->generate($site->getRef() . '_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return new RedirectResponse($redirectUrl);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
