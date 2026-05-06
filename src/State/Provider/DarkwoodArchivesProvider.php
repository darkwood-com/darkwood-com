<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\ApiKey;
use App\Repository\DarkwoodArchiveRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class DarkwoodArchivesProvider implements ProviderInterface
{
    public function __construct(
        private DarkwoodArchiveRepository $archiveRepository,
        private RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $apiKey = $request?->attributes->get('api_key');

        if (!$apiKey instanceof ApiKey || !$apiKey->isPremium()) {
            throw new AccessDeniedHttpException('Premium access required');
        }

        $items = [];
        foreach ($this->archiveRepository->findAllOrderByDateDesc() as $archive) {
            $items[] = [
                'id' => $archive->getDateId(),
                'date' => $archive->getDateId(),
            ];
        }

        return new JsonResponse(['archives' => $items]);
    }
}
