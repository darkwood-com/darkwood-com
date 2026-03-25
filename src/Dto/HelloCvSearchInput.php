<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * MCP input for hello_search_cv (substring match, case-insensitive).
 */
final class HelloCvSearchInput
{
    public function __construct(
        public ?string $query = null,
    ) {}
}
