<?php

declare(strict_types=1);

namespace App\ApiResource;

use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Named product/system entry (Flow, Uniflow, Darkwood) for machine-readable CV graphs.
 */
final readonly class HelloCvSystem
{
    /**
     * @param list<array{label: string, url: string}> $links
     */
    public function __construct(
        #[Groups(['cv:read'])]
        public string $id,
        #[Groups(['cv:read'])]
        public string $name,
        #[Groups(['cv:read'])]
        public string $description,
        #[Groups(['cv:read'])]
        public array $links,
    ) {}
}
