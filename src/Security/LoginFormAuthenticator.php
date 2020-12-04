<?php

namespace App\Security;

use App\Entity\User;
use App\Services\SiteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use \Symfony\Component\Security\Http\Util\TargetPathTrait;
    public const LOGIN_ROUTE = 'security_login';
    public function __construct(
        private \Doctrine\ORM\EntityManagerInterface $entityManager,
        private \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator,
        private \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $csrfTokenManager,
        private \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder,
        /**
         * @var SiteService
         */
        private SiteService $siteService,
        private \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
    )
    {
    }
    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('POST');
    }
    public function getCredentials(Request $request)
    {
        $credentials = ['username' => $request->request->get('username'), 'password' => $request->request->get('password'), 'csrf_token' => $request->request->get('_csrf_token')];
        $request->getSession()->set(\Symfony\Component\Security\Core\Security::LAST_USERNAME, $credentials['username']);
        return $credentials;
    }
    public function getUser($credentials, \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider)
    {
        $token = new \Symfony\Component\Security\Csrf\CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $user = $this->entityManager->getRepository(User::class)->loadUserByUsername($credentials['username']);
        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }
        return $user;
    }
    public function checkCredentials($credentials, \Symfony\Component\Security\Core\User\UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }
    public function onAuthenticationSuccess(Request $request, \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new \Symfony\Component\HttpFoundation\RedirectResponse($targetPath);
        }
        $redirectUrl = $request->headers->get('Referer');
        if (str_contains($redirectUrl, 'login')) {
            $redirectUrl = $this->urlGenerator->generate('darkwood_home', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            $host = $request->getHost();
            $site = $this->siteService->findOneByHost($host);
            if ($host == $this->parameterBag->get('admin_host')) {
                $redirectUrl = $this->urlGenerator->generate('admin_home', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            } elseif ($site) {
                $redirectUrl = $this->urlGenerator->generate($site->getRef() . '_home', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }
        return new \Symfony\Component\HttpFoundation\RedirectResponse($redirectUrl);
    }
    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
