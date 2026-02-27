<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\EntitlementRepository;

/**
 * Minimal entitlement service for Darkwood API monetization.
 * - dev/test: monetization disabled, everyone treated as premium.
 * - prod: premium only if active entitlement exists.
 * - test with DARKWOOD_MONETIZATION_SIMULATE_PROD=1: same as prod (for tests).
 */
class DarkwoodEntitlementService
{
    public function __construct(
        private readonly EntitlementRepository $entitlementRepository,
        private readonly string $kernelEnvironment,
    ) {
    }

    public function isPremium(?User $user): bool
    {
        if (!$this->isMonetizationEnabled()) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $this->entitlementRepository->findActivePremiumForUser($user) !== null;
    }

    public function isMonetizationEnabled(): bool
    {
        if ($this->kernelEnvironment === 'prod') {
            return true;
        }

        return getenv('DARKWOOD_MONETIZATION_SIMULATE_PROD') !== false
            && getenv('DARKWOOD_MONETIZATION_SIMULATE_PROD') !== '';
    }
}
