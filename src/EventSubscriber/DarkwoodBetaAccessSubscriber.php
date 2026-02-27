<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\ApiKey;
use App\Services\ApiKeyResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Beta access gate for /api/darkwood/* endpoints.
 * Requires valid X-API-Key header with an active, beta-enabled key.
 */
final class DarkwoodBetaAccessSubscriber implements EventSubscriberInterface
{
    private const PATH_PREFIX = '/api/darkwood';

    public function __construct(
        private readonly ApiKeyResolver $apiKeyResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 12],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, self::PATH_PREFIX)) {
            return;
        }

        $apiKey = $this->apiKeyResolver->resolveForGate($request);

        if ($apiKey === null) {
            $event->setResponse(new JsonResponse([
                'error' => 'missing_or_invalid_api_key',
            ], Response::HTTP_UNAUTHORIZED));

            return;
        }

        if (!$apiKey->isActive()) {
            $event->setResponse(new JsonResponse([
                'error' => 'api_key_inactive',
            ], Response::HTTP_FORBIDDEN));

            return;
        }

        if (!$apiKey->isBeta()) {
            $event->setResponse(new JsonResponse([
                'error' => 'beta_access_required',
            ], Response::HTTP_FORBIDDEN));

            return;
        }

        $request->attributes->set('api_key', $apiKey);
    }
}
