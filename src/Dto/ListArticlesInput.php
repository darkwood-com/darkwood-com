<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Input for MCP tool list_articles.
 */
final class ListArticlesInput
{
    public function __construct(
        public ?int $limit = 30,
        public ?string $locale = 'en',
    ) {}
}
