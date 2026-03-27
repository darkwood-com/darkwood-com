<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\HelloCvSkillsProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'HelloSkill',
    operations: [
        new GetCollection(
            uriTemplate: '/hello_skills',
            provider: HelloCvSkillsProvider::class,
            name: 'api_hello_skills_get_collection',
            security: "is_granted('PUBLIC_ACCESS')",
            paginationEnabled: false,
        ),
    ],
    normalizationContext: ['groups' => ['cv:read']],
)]
final readonly class HelloCvSkill
{
    public function __construct(
        #[Groups(['cv:read'])]
        public string $id,
        #[Groups(['cv:read'])]
        public string $name,
        #[Groups(['cv:read'])]
        public string $category,
    ) {}
}
