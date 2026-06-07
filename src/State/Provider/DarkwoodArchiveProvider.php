<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\ApiKey;
use App\Entity\DarkwoodArchive;
use App\Repository\DarkwoodArchiveRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DarkwoodArchiveProvider implements ProviderInterface
{
    public function __construct(
        private DarkwoodArchiveRepository $archiveRepository,
        private RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|JsonResponse|null
    {
        $request = $this->requestStack->getCurrentRequest();
        $apiKey = $request?->attributes->get('api_key');

        if (!$apiKey instanceof ApiKey || !$apiKey->isPremium()) {
            throw new AccessDeniedHttpException('Premium access required');
        }

        $id = (string) ($uriVariables['id'] ?? '');
        $archive = $this->archiveRepository->findOneByDateId($id);
        if (!$archive instanceof DarkwoodArchive) {
            throw new NotFoundHttpException('Archive not found');
        }

        return new JsonResponse($archive->getPayload());
    }
}
