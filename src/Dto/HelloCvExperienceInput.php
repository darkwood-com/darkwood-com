<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * MCP input for hello_get_experience: match by stable id or company name (case-insensitive for company).
 */
final class HelloCvExperienceInput
{
    public function __construct(
        public ?string $id = null,
        public ?string $company = null,
    ) {}
}
