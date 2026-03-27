<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\SiteService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TwoFactorSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    private const FIREWALL_NAME = 'main';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SiteService $siteService,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $session = $request->getSession();
        $targetPath = $this->getTargetPath($session, self::FIREWALL_NAME);

        if (null !== $targetPath) {
            $this->removeTargetPath($session, self::FIREWALL_NAME);

            return new RedirectResponse($targetPath);
        }

        $locale = $request->getLocale() ?: 'en';
        $host = $request->getHost();

        if ($host === $this->parameterBag->get('admin_host')) {
            return new RedirectResponse($this->urlGenerator->generate('admin_home', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        $site = $this->siteService->findOneByHost($host);
        $route = $site !== null ? $site->getRef() . '_home' : 'darkwood_home';

        return new RedirectResponse($this->urlGenerator->generate($route, ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
