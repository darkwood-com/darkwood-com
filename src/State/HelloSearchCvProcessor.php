<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\HelloCvSearchInput;
use App\Service\HelloCvRepositoryService;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MCP: deterministic search across experiences, projects, skills.
 */
final readonly class HelloSearchCvProcessor implements ProcessorInterface
{
    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
        private NormalizerInterface $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $query = '';
        if ($data instanceof HelloCvSearchInput && $data->query !== null) {
            $query = $data->query;
        }

        $result = $this->helloCvRepository->searchCv($query);

        $experiences = [];
        foreach ($result['experiences'] as $exp) {
            /** @var array<string, mixed> $row */
            $row = $this->normalizer->normalize($exp, null, [
                AbstractNormalizer::GROUPS => ['cv:read'],
            ]);
            $experiences[] = $row;
        }

        $projects = [];
        foreach ($result['projects'] as $proj) {
            /** @var array<string, mixed> $row */
            $row = $this->normalizer->normalize($proj, null, [
                AbstractNormalizer::GROUPS => ['cv:read'],
            ]);
            $projects[] = $row;
        }

        $skills = [];
        foreach ($result['skills'] as $skill) {
            /** @var array<string, mixed> $row */
            $row = $this->normalizer->normalize($skill, null, [
                AbstractNormalizer::GROUPS => ['cv:read'],
            ]);
            $skills[] = $row;
        }

        return [
            'query' => $result['query'],
            'experiences' => $experiences,
            'projects' => $projects,
            'skills' => $skills,
        ];
    }
}
