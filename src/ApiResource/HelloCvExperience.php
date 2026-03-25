<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\HelloCvExperiencesProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'HelloExperience',
    operations: [
        new GetCollection(
            uriTemplate: '/hello_experiences',
            provider: HelloCvExperiencesProvider::class,
            name: 'api_hello_experiences_get_collection',
            security: "is_granted('PUBLIC_ACCESS')",
            paginationEnabled: false,
        ),
    ],
    normalizationContext: ['groups' => ['cv:read']],
)]
final readonly class HelloCvExperience
{
    /**
     * @param list<string> $stack
     * @param list<string> $highlights
     */
    public function __construct(
        #[Groups(['cv:read'])]
        public string $id,
        #[Groups(['cv:read'])]
        public string $company,
        #[Groups(['cv:read'])]
        public string $role,
        #[Groups(['cv:read'])]
        public string $startDate,
        #[Groups(['cv:read'])]
        public ?string $endDate,
        #[Groups(['cv:read'])]
        public string $description,
        #[Groups(['cv:read'])]
        public array $stack,
        #[Groups(['cv:read'])]
        public array $highlights,
    ) {}
}
