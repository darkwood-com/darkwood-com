<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\HelloCvProject;
use App\Service\HelloCvRepository;

/**
 * @implements ProviderInterface<HelloCvProject>
 */
final readonly class HelloCvProjectsProvider implements ProviderInterface
{
    public function __construct(
        private HelloCvRepository $helloCvRepository,
    ) {}

    /**
     * @return list<HelloCvProject>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->helloCvRepository->getProjects();
    }
}
