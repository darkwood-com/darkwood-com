<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Security\WebAuthenticationHostResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Mathieu Ledru
 */
final readonly class ApiHostAuthenticationRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WebAuthenticationHostResolver $authenticationHostResolver,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->authenticationHostResolver->shouldMirrorToAuthenticationHost($request)) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $this->authenticationHostResolver->mirrorUrl($request),
            RedirectResponse::HTTP_FOUND,
        ));
    }
}
