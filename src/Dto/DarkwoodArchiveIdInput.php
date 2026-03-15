<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Input for MCP tool get_darkwood_archive. Archive date ID (Y-m-d).
 */
final class DarkwoodArchiveIdInput
{
    public function __construct(
        public string $id = '',
    ) {}
}
