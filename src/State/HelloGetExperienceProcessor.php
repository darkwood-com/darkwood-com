<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\HelloCvExperienceInput;
use App\Service\HelloCvRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * MCP: single experience or not found.
 */
final readonly class HelloGetExperienceProcessor implements ProcessorInterface
{
    public function __construct(
        private HelloCvRepository $helloCvRepository,
        private NormalizerInterface $normalizer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $exp = null;
        if ($data instanceof HelloCvExperienceInput) {
            if ($data->id !== null && $data->id !== '') {
                $exp = $this->helloCvRepository->getExperienceById($data->id);
            } elseif ($data->company !== null && $data->company !== '') {
                $exp = $this->helloCvRepository->getExperienceByCompany($data->company);
            }
        }

        if ($exp === null) {
            return ['matched' => false, 'experience' => null];
        }

        /** @var array<string, mixed> $normalized */
        $normalized = $this->normalizer->normalize($exp, null, [
            AbstractNormalizer::GROUPS => ['cv:read'],
        ]);

        return ['matched' => true, 'experience' => $normalized];
    }
}
