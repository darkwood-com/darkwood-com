<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\HelloCvSkill;
use App\Service\HelloCvRepositoryService;

/**
 * @implements ProviderInterface<HelloCvSkill>
 */
final readonly class HelloCvSkillsProvider implements ProviderInterface
{
    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
    ) {}

    /**
     * @return list<HelloCvSkill>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $skills = $this->helloCvRepository->getSkills();
        usort($skills, static function (HelloCvSkill $a, HelloCvSkill $b): int {
            $c = strcmp($a->category, $b->category);

            return $c !== 0 ? $c : strcmp($a->name, $b->name);
        });

        return $skills;
    }
}
