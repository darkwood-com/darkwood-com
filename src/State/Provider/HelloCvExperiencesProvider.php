<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\HelloCvExperience;
use App\Service\HelloCvRepositoryService;

/**
 * @implements ProviderInterface<HelloCvExperience>
 */
final readonly class HelloCvExperiencesProvider implements ProviderInterface
{
    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
    ) {}

    /**
     * @return list<HelloCvExperience>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->helloCvRepository->getExperiences();
    }
}
