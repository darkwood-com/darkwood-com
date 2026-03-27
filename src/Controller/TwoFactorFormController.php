<?php

declare(strict_types=1);

namespace App\Controller;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Exception\UnknownTwoFactorProviderException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

use function str_contains;

class TwoFactorFormController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TwoFactorProviderRegistry $providerRegistry,
        private readonly TwoFactorFirewallContext $twoFactorFirewallContext,
        private readonly LogoutUrlGenerator $logoutUrlGenerator,
        private readonly CommonController $commonController,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function form(Request $request): Response
    {
        $token = $this->getTwoFactorToken();
        $this->setPreferredProvider($request, $token);

        $providerName = $token->getCurrentTwoFactorProvider();
        if (null === $providerName) {
            throw new AccessDeniedException('User is not in a two-factor authentication process.');
        }

        $renderer = $this->providerRegistry->getProvider($providerName)->getFormRenderer();

        return $renderer->renderForm($request, $this->getTemplateVars($request, $token));
    }

    /**
     * @return array<string, mixed>
     */
    private function getTemplateVars(Request $request, TwoFactorTokenInterface $token): array
    {
        $config = $this->twoFactorFirewallContext->getFirewallConfig($token->getFirewallName());
        $authenticationException = $this->getLastAuthenticationException($request->getSession());
        $checkPath = $config->getCheckPath();
        $isRoute = !str_contains($checkPath, '/');
        $siteRef = $this->buildSiteRef($request);
        $templateVars = [
            'twoFactorProvider' => $token->getCurrentTwoFactorProvider(),
            'availableTwoFactorProviders' => $token->getTwoFactorProviders(),
            'authenticationError' => $authenticationException?->getMessageKey(),
            'authenticationErrorData' => $authenticationException?->getMessageData(),
            'displayTrustedOption' => false,
            'authCodeParameterName' => $config->getAuthCodeParameterName(),
            'trustedParameterName' => $config->getTrustedParameterName(),
            'isCsrfProtectionEnabled' => $config->isCsrfProtectionEnabled(),
            'csrfParameterName' => $config->getCsrfParameterName(),
            'csrfTokenId' => $config->getCsrfTokenId(),
            'checkPathRoute' => $isRoute ? $checkPath : null,
            'checkPathUrl' => $isRoute ? null : $checkPath,
            'logoutPath' => $this->logoutUrlGenerator->getLogoutPath(),
            'site_ref' => $siteRef,
        ];

        if ('admin' !== $siteRef) {
            $templateVars['page'] = $this->commonController->getPage($request, 'login');
            $templateVars['showLinks'] = true;
        }

        return $templateVars;
    }

    private function getTwoFactorToken(): TwoFactorTokenInterface
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TwoFactorTokenInterface) {
            throw new AccessDeniedException('User is not in a two-factor authentication process.');
        }

        return $token;
    }

    private function setPreferredProvider(Request $request, TwoFactorTokenInterface $token): void
    {
        $preferredProvider = (string) $request->query->get('preferProvider');
        if ('' === $preferredProvider) {
            return;
        }

        try {
            $token->preferTwoFactorProvider($preferredProvider);
        } catch (UnknownTwoFactorProviderException) {
            // Ignore invalid user input.
        }
    }

    private function getLastAuthenticationException(SessionInterface $session): ?AuthenticationException
    {
        $authException = $session->get(SecurityRequestAttributes::AUTHENTICATION_ERROR);
        if ($authException instanceof AuthenticationException) {
            $session->remove(SecurityRequestAttributes::AUTHENTICATION_ERROR);

            return $authException;
        }

        return null;
    }

    private function buildSiteRef(Request $request): string
    {
        if ($request->getHost() === $this->parameterBag->get('admin_host')) {
            return 'admin';
        }

        return $this->commonController->getPage($request, 'login')->getPage()->getSite()->getRef();
    }
}
