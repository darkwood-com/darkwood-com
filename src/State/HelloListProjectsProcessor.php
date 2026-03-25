<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\HelloCvRepositoryService;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MCP: list of projects.
 */
final readonly class HelloListProjectsProcessor implements ProcessorInterface
{
    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
        private NormalizerInterface $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $out = [];
        foreach ($this->helloCvRepository->getProjects() as $proj) {
            /** @var array<string, mixed> $row */
            $row = $this->normalizer->normalize($proj, null, [
                AbstractNormalizer::GROUPS => ['cv:read'],
            ]);
            $out[] = $row;
        }

        return ['projects' => $out];
    }
}
