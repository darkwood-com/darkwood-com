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
            stateless: false,
            name: 'api_darkwood_get_state',
            provider: DarkwoodStateProvider::class,
        ),
        new Post(
            uriTemplate: '/darkwood/action',
            inputFormats: ['json' => ['application/json', 'application/x-www-form-urlencoded']],
            stateless: false,
            input: DarkwoodActionInput::class,
            read: false,
            name: 'api_darkwood_post_action',
            processor: DarkwoodActionHttpProcessor::class,
        ),
        new Get(
            uriTemplate: '/darkwood/archives',
            stateless: false,
            name: 'api_darkwood_archives',
            provider: DarkwoodArchivesProvider::class,
        ),
        new Get(
            uriTemplate: '/darkwood/archives/{id}',
            stateless: false,
            name: 'api_darkwood_archive_get',
            provider: DarkwoodArchiveProvider::class,
        ),
    ],
    mcp: [
        'get_darkwood_state' => new McpTool(
            description: 'Get the current Darkwood game state for the authenticated user.',
            structuredContent: true,
            processor: DarkwoodStateProcessor::class,
        ),
        'darkwood_action' => new McpTool(
            description: 'Execute a state-changing action in the Darkwood game. Optionally pass query parameters.',
            structuredContent: true,
            input: DarkwoodActionInput::class,
            processor: DarkwoodActionProcessor::class,
        ),
        'list_darkwood_archives' => new McpTool(
            description: 'List available Darkwood save archives (premium). Returns archive id and date.',
            structuredContent: true,
            processor: DarkwoodArchivesProcessor::class,
        ),
        'get_darkwood_archive' => new McpTool(
            description: 'Get a single Darkwood archive payload by date ID (Y-m-d format). Premium only.',
            structuredContent: true,
            input: DarkwoodArchiveIdInput::class,
            processor: DarkwoodArchiveGetProcessor::class,
        ),
    ],
)]
final class DarkwoodGame {}
