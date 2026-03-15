<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Input for MCP tool darkwood_action. Query params to pass to the game engine.
 */
final class DarkwoodActionInput
{
    public function __construct(
        #[ApiProperty(schema: ['type' => 'object', 'description' => 'Query parameters as key-value map'])]
        public ?array $query = null,
    ) {}
}
