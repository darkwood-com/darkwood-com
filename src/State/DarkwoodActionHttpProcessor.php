<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Service\DarkwoodGameService;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function is_array;

final readonly class DarkwoodActionHttpProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodGameService $gameService,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private DarkwoodResultNormalizer $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new BadRequestHttpException('No current request.');
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            throw new BadRequestHttpException('Could not decode request body.');
        }

        $queryParams = $payload['query'] ?? [];
        if (!is_array($queryParams)) {
            $queryParams = [];
        }

        foreach ($queryParams as $key => $value) {
            $request->query->set((string) $key, $value);
        }

        $result = $this->gameService->play($request, null, $this->getCurrentUser());

        return new JsonResponse($this->normalizer->normalize($result));
    }

    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        return $user instanceof User ? $user : null;
    }
}
