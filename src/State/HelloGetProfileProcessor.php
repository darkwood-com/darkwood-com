<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\HelloCvRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MCP: structured profile object (JSON-serializable array).
 */
final readonly class HelloGetProfileProcessor implements ProcessorInterface
{
    public function __construct(
        private HelloCvRepository $helloCvRepository,
        private NormalizerInterface $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $profile = $this->helloCvRepository->getProfile();

        /** @var array<string, mixed> $normalized */
        return $this->normalizer->normalize($profile, null, [
            AbstractNormalizer::GROUPS => ['cv:read'],
        ]);
    }
}
