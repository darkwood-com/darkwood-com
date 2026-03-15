<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Input for MCP tool get_article.
 */
final class ArticleIdInput
{
    public function __construct(
        public int $id = 0,
    ) {}
}
