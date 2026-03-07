<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\ApiKey;
use App\Repository\ApiKeyUsageRepository;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DarkwoodRateLimitSubscriber implements EventSubscriberInterface
{
    private const ROUTE = 'api_darkwood_post_action';

    public function __construct(
        private readonly ApiKeyUsageRepository $apiKeyUsageRepository,
    ) {}

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

        $apiKey = $request->attributes->get('api_key');
        if (!$apiKey instanceof ApiKey) {
            $event->setResponse(new JsonResponse([
                'error' => 'missing_or_invalid_api_key',
                'message' => 'A valid API key is required',
            ], Response::HTTP_UNAUTHORIZED));

            return;
        }

        if ($apiKey->isPremium()) {
            return;
        }

        $dailyLimit = $apiKey->getDailyActionLimit();
        if ($dailyLimit === null) {
            return;
        }

        $nowUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $todayUtc = $nowUtc->setTime(0, 0, 0);

        if ($dailyLimit <= 0 || !$this->apiKeyUsageRepository->incrementIfBelowLimit($apiKey, $todayUtc, $dailyLimit)) {
            $nextMidnightUtc = $todayUtc->modify('+1 day');
            $retryAfter = max(0, $nextMidnightUtc->getTimestamp() - $nowUtc->getTimestamp());

            $event->setResponse(new JsonResponse([
                'error' => 'rate_limited',
                'message' => 'Daily action limit reached',
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => (string) $retryAfter,
            ]));
        }
    }
}
