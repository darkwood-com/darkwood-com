<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\HelloCvSkill;
use App\Service\HelloCvRepositoryService;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MCP: skills sorted by category then name.
 */
final readonly class HelloListSkillsProcessor implements ProcessorInterface
{
    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
        private NormalizerInterface $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $skills = $this->helloCvRepository->getSkills();
        usort($skills, static function (HelloCvSkill $a, HelloCvSkill $b): int {
            $c = strcmp($a->category, $b->category);

            return $c !== 0 ? $c : strcmp($a->name, $b->name);
        });

        $out = [];
        foreach ($skills as $skill) {
            /** @var array<string, mixed> $row */
            $row = $this->normalizer->normalize($skill, null, [
                AbstractNormalizer::GROUPS => ['cv:read'],
            ]);
            $out[] = $row;
        }

        return ['skills' => $out];
    }
}
