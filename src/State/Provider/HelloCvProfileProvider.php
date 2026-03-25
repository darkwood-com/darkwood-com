<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\HelloCvProfile;
use App\Service\HelloCvRepositoryService;

/**
 * @implements ProviderInterface<HelloCvProfile>
 */
final readonly class HelloCvProfileProvider implements ProviderInterface
{
    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?HelloCvProfile
    {
        return $this->helloCvRepository->getProfile();
    }
}
