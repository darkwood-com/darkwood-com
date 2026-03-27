<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Provider\HelloCvProfileProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'HelloProfile',
    operations: [
        new Get(
            uriTemplate: '/hello_profile',
            provider: HelloCvProfileProvider::class,
            name: 'api_hello_profile_get',
            security: "is_granted('PUBLIC_ACCESS')",
        ),
    ],
    normalizationContext: ['groups' => ['cv:read']],
)]
final readonly class HelloCvProfile
{
    /**
     * @param list<array{label: string, url: string}> $links
     * @param list<HelloCvSystem>                     $systems
     */
    public function __construct(
        #[Groups(['cv:read'])]
        public string $name,
        #[Groups(['cv:read'])]
        public string $role,
        #[Groups(['cv:read'])]
        public string $summary,
        #[Groups(['cv:read'])]
        public string $location,
        #[Groups(['cv:read'])]
        public array $links,
        #[Groups(['cv:read'])]
        public array $systems = [],
    ) {}
}
