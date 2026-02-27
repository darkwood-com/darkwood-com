<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Services\DarkwoodEntitlementService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Applies rate limiting to POST /api/darkwood/action in prod only.
 * Anonymous / free / premium get different limits; premium effectively unlimited.
 */
final class DarkwoodRateLimitSubscriber implements EventSubscriberInterface
{
    private const ROUTE = 'api_darkwood_post_action';

    public function __construct(
        private readonly DarkwoodEntitlementService $entitlementService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RateLimiterFactory $darkwoodActionAnonymousLimiter,
        private readonly RateLimiterFactory $darkwoodActionAuthenticatedLimiter,
        private readonly RateLimiterFactory $darkwoodActionPremiumLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->attributes->get('_route') !== self::ROUTE) {
            return;
        }

        $user = $this->getCurrentUser();
        $isPremium = $this->entitlementService->isPremium($user);

        if ($isPremium) {
            $factory = $this->darkwoodActionPremiumLimiter;
            $key = $user !== null ? 'user_' . $user->getId() : 'anon_' . $request->getClientIp();
        } elseif ($user instanceof User) {
            $factory = $this->darkwoodActionAuthenticatedLimiter;
            $key = 'user_' . $user->getId();
        } else {
            $factory = $this->darkwoodActionAnonymousLimiter;
            $key = 'anon_' . $request->getClientIp();
        }

        $limiter = $factory->create($key);
        $limit = $limiter->consume(1);

        if ($limit->isAccepted()) {
            return;
        }

        $retryAfter = $limit->getRetryAfter() !== null
            ? (int) ($limit->getRetryAfter()->getTimestamp() - time())
            : 3600;

        $event->setResponse(new JsonResponse([
            'error' => 'rate_limited',
            'message' => 'Daily action limit reached',
            'retryAfter' => max(0, $retryAfter),
        ], Response::HTTP_TOO_MANY_REQUESTS, [
            'Retry-After' => (string) max(0, $retryAfter),
        ]));
    }

    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        return $user instanceof User ? $user : null;
    }
}
