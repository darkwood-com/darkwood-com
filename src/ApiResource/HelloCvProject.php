<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\HelloCvProjectsProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'HelloProject',
    operations: [
        new GetCollection(
            uriTemplate: '/hello_projects',
            provider: HelloCvProjectsProvider::class,
            name: 'api_hello_projects_get_collection',
            security: "is_granted('PUBLIC_ACCESS')",
            paginationEnabled: false,
        ),
    ],
    normalizationContext: ['groups' => ['cv:read']],
)]
final readonly class HelloCvProject
{
    /**
     * @param list<array{label: string, url: string}> $links
     * @param list<string>                            $tags
     */
    public function __construct(
        #[Groups(['cv:read'])]
        public string $id,
        #[Groups(['cv:read'])]
        public string $name,
        #[Groups(['cv:read'])]
        public string $description,
        #[Groups(['cv:read'])]
        public string $type,
        #[Groups(['cv:read'])]
        public array $links,
        #[Groups(['cv:read'])]
        public array $tags,
        #[Groups(['cv:read'])]
        public ?string $imageAsset = null,
    ) {}
}
