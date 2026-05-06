<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\Post;
use App\Dto\DarkwoodActionInput;
use App\Dto\DarkwoodArchiveIdInput;
use App\State\DarkwoodActionHttpProcessor;
use App\State\DarkwoodActionProcessor;
use App\State\DarkwoodArchiveGetProcessor;
use App\State\DarkwoodArchivesProcessor;
use App\State\DarkwoodStateProcessor;
use App\State\Provider\DarkwoodArchiveProvider;
use App\State\Provider\DarkwoodArchivesProvider;
use App\State\Provider\DarkwoodStateProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/darkwood/state',
            provider: DarkwoodStateProvider::class,
            name: 'api_darkwood_get_state',
            stateless: false,
        ),
        new Post(
            uriTemplate: '/darkwood/action',
            processor: DarkwoodActionHttpProcessor::class,
            read: false,
            input: DarkwoodActionInput::class,
            inputFormats: ['json' => ['application/json', 'application/x-www-form-urlencoded']],
            name: 'api_darkwood_post_action',
            stateless: false,
        ),
        new Get(
            uriTemplate: '/darkwood/archives',
            provider: DarkwoodArchivesProvider::class,
            name: 'api_darkwood_archives',
            stateless: false,
        ),
        new Get(
            uriTemplate: '/darkwood/archives/{id}',
            provider: DarkwoodArchiveProvider::class,
            name: 'api_darkwood_archive_get',
            stateless: false,
        ),
    ],
    mcp: [
        'get_darkwood_state' => new McpTool(
            description: 'Get the current Darkwood game state for the authenticated user.',
            processor: DarkwoodStateProcessor::class,
            structuredContent: true,
        ),
        'darkwood_action' => new McpTool(
            description: 'Execute a state-changing action in the Darkwood game. Optionally pass query parameters.',
            input: DarkwoodActionInput::class,
            processor: DarkwoodActionProcessor::class,
            structuredContent: true,
        ),
        'list_darkwood_archives' => new McpTool(
            description: 'List available Darkwood save archives (premium). Returns archive id and date.',
            processor: DarkwoodArchivesProcessor::class,
            structuredContent: true,
        ),
        'get_darkwood_archive' => new McpTool(
            description: 'Get a single Darkwood archive payload by date ID (Y-m-d format). Premium only.',
            input: DarkwoodArchiveIdInput::class,
            processor: DarkwoodArchiveGetProcessor::class,
            structuredContent: true,
        ),
    ],
)]
final class DarkwoodGame {}
