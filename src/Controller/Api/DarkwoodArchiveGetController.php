<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ApiKey;
use App\Repository\DarkwoodArchiveRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
final class DarkwoodArchiveGetController
{
    public function __construct(
        private readonly DarkwoodArchiveRepository $archiveRepository,
    ) {}

    public function __invoke(Request $request, string $id): Response
    {
        $apiKey = $request->attributes->get('api_key');

        if (!$apiKey instanceof ApiKey || !$apiKey->isPremium()) {
            return new JsonResponse([
                'error' => 'premium_required',
                'message' => 'Premium access required',
            ], Response::HTTP_FORBIDDEN);
        }

        $archive = $this->archiveRepository->findOneByDateId($id);
        if ($archive === null) {
            return new JsonResponse([
                'error' => 'archive_not_found',
                'message' => 'Archive not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($archive->getPayload());
    }
}
