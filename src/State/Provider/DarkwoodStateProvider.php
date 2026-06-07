<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Service\DarkwoodGameService;
use App\State\DarkwoodResultNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class DarkwoodStateProvider implements ProviderInterface
{
    public function __construct(
        private DarkwoodGameService $gameService,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private DarkwoodResultNormalizer $normalizer,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|JsonResponse|null
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof \Symfony\Component\HttpFoundation\Request) {
            return null;
        }

        $result = $this->gameService->play($request, null, $this->getCurrentUser());

        return new JsonResponse($this->normalizer->normalize($result));
    }

    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token instanceof \Symfony\Component\Security\Core\Authentication\Token\TokenInterface ? $token->getUser() : null;

        return $user instanceof User ? $user : null;
    }
}
