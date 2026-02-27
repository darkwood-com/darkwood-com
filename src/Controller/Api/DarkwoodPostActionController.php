<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Services\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function is_array;

#[AsController]
final class DarkwoodPostActionController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getCurrentUser();

        $payload = [];
        try {
            $payload = $request->toArray();
        } catch (\JsonException) {
            $payload = [];
        }

        $queryParams = $payload['query'] ?? [];
        if (!is_array($queryParams)) {
            $queryParams = [];
        }

        foreach ($queryParams as $key => $value) {
            $request->query->set((string) $key, $value);
        }

        $result = $this->gameService->play($request, null, $user);

        if ($result instanceof Response) {
            return $result;
        }

        $normalized = $this->normalizeResult($result);

        return $this->json($normalized);
    }

    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        return $user instanceof User ? $user : null;
    }

    private function normalizeResult(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DATE_ATOM);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeResult($item);
            }

            return $normalized;
        }

        if ($value instanceof \Traversable) {
            $normalized = [];
            foreach ($value as $item) {
                $normalized[] = $this->normalizeResult($item);
            }

            return $normalized;
        }

        if (method_exists($value, 'getId')) {
            return $value->getId();
        }

        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }
}

