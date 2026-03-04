<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\ApiKey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DarkwoodArchivesController
{
    public function __invoke(Request $request): Response
    {
        $apiKey = $request->attributes->get('api_key');

        if (!$apiKey instanceof ApiKey || !$apiKey->isPremium()) {
            return new JsonResponse([
                'error' => 'premium_required',
                'message' => 'Premium access required',
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'archives' => [],
        ]);
    }
}
