<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Services\DarkwoodEntitlementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Premium-only meta endpoint. In dev/test always accessible; in prod requires premium.
 * Returns stub (empty array) or existing historical data without adding game logic.
 */
#[AsController]
final class DarkwoodArchivesController extends AbstractController
{
    public function __construct(
        private readonly DarkwoodEntitlementService $entitlementService,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function __invoke(): Response
    {
        $user = $this->getCurrentUser();

        if (!$this->entitlementService->isPremium($user)) {
            return new JsonResponse([
                'error' => 'forbidden',
                'message' => 'Premium access required',
            ], Response::HTTP_FORBIDDEN);
        }

        // Stub: no new gameplay. Return empty archives or existing historical data when available.
        return new JsonResponse([]);
    }

    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        return $user instanceof User ? $user : null;
    }
}
