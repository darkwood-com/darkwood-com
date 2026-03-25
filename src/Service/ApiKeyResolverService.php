<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Resolves and validates API key from X-API-Key header.
 * Returns ApiKey entity only when key exists and is active.
 */
class ApiKeyResolverService
{
    public const HEADER_NAME = 'X-API-Key';

    public function __construct(
        private readonly ApiKeyRepository $apiKeyRepository,
    ) {}

    public function resolve(Request $request): ?ApiKey
    {
        $rawKey = $request->headers->get(self::HEADER_NAME);
        if ($rawKey === null || $rawKey === '') {
            return null;
        }

        $keyHash = hash('sha256', $rawKey);
        $apiKey = $this->apiKeyRepository->findOneByKeyHash($keyHash);
        if ($apiKey === null) {
            return null;
        }

        if (!$apiKey->isActive()) {
            return $apiKey;
        }

        return $apiKey;
    }

    /**
     * Returns the ApiKey if the key was found (even inactive); null if key missing or invalid.
     * Used by the beta gate to distinguish 401 (not found) vs 403 (found but inactive/not beta).
     * On any lookup error (e.g. DB unavailable), returns null so the gate returns 401.
     */
    public function resolveForGate(Request $request): ?ApiKey
    {
        $rawKey = $request->headers->get(self::HEADER_NAME);
        if ($rawKey === null || $rawKey === '') {
            return null;
        }

        try {
            $keyHash = hash('sha256', $rawKey);

            return $this->apiKeyRepository->findOneByKeyHash($keyHash);
        } catch (Throwable) {
            return null;
        }
    }
}
