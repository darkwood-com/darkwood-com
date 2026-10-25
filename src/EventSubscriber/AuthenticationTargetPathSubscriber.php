<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Security\WebAuthenticationHostResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use function is_string;

/**
 * Auth target paths saved on the API host must be replayed on the Darkwood site host.
 *
 * @author Mathieu Ledru
 */
final readonly class AuthenticationTargetPathSubscriber implements EventSubscriberInterface
{
    private const string TARGET_PATH_KEY = '_security.main.target_path';

    public function __construct(
        private WebAuthenticationHostResolver $authenticationHostResolver,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 90],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if (!$session->isStarted()) {
            return;
        }

        $targetPath = $session->get(self::TARGET_PATH_KEY);
        if (!is_string($targetPath) || '' === $targetPath) {
            return;
        }

        $mirroredTargetPath = $this->authenticationHostResolver->mirrorAuthenticationUri($targetPath);
        if ($mirroredTargetPath !== $targetPath) {
            $session->set(self::TARGET_PATH_KEY, $mirroredTargetPath);
        }
    }
}
