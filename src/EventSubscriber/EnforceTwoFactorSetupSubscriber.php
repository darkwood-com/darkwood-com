<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EnforceTwoFactorSetupSubscriber implements EventSubscriberInterface
{
    private const EXCLUDED_ROUTES = [
        'app_2fa_setup',
        'security_login',
        'security_logout',
        '2fa_login',
        '2fa_login_check',
    ];

    public function __construct(
        private readonly Security $security,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS')) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user->isTotpAuthenticationEnabled()) {
            return;
        }

        $route = (string) $event->getRequest()->attributes->get('_route');
        if (\in_array($route, self::EXCLUDED_ROUTES, true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_2fa_setup')));
    }
}
