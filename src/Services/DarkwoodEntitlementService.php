<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\EntitlementRepository;

/**
 * Minimal entitlement service: premium only if active entitlement exists for the user.
 * No environment-based behavior.
 */
class DarkwoodEntitlementService
{
    public function __construct(
        private readonly EntitlementRepository $entitlementRepository,
    ) {
    }

    public function isPremium(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->entitlementRepository->findActivePremiumForUser($user) !== null;
    }
}
