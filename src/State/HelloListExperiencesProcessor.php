<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\HelloCvRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MCP: list of experiences.
 */
final readonly class HelloListExperiencesProcessor implements ProcessorInterface
{
    public function __construct(
        private HelloCvRepository $helloCvRepository,
        private NormalizerInterface $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $out = [];
        foreach ($this->helloCvRepository->getExperiences() as $exp) {
            /** @var array<string, mixed> $row */
            $row = $this->normalizer->normalize($exp, null, [
                AbstractNormalizer::GROUPS => ['cv:read'],
            ]);
            $out[] = $row;
        }

        return ['experiences' => $out];
    }
}
