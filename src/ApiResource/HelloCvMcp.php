<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\McpTool;
use App\Dto\HelloCvExperienceInput;
use App\Dto\HelloCvSearchInput;
use App\State\HelloGetExperienceProcessor;
use App\State\HelloGetProfileProcessor;
use App\State\HelloListExperiencesProcessor;
use App\State\HelloListProjectsProcessor;
use App\State\HelloListSkillsProcessor;
use App\State\HelloSearchCvProcessor;

/**
 * MCP-only surface for Hello CV tools (no extra HTTP routes).
 */
#[ApiResource(
    shortName: 'HelloCvMcp',
    operations: [],
    mcp: [
        'hello_get_profile' => new McpTool(
            description: 'Return CV profile: name, role, summary, location, links, systems.',
            processor: HelloGetProfileProcessor::class,
            structuredContent: true,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
        'hello_list_experiences' => new McpTool(
            description: 'List work experiences (id, company, dates, stack, highlights).',
            processor: HelloListExperiencesProcessor::class,
            structuredContent: true,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
        'hello_get_experience' => new McpTool(
            description: 'Get one experience by id or company name.',
            input: HelloCvExperienceInput::class,
            processor: HelloGetExperienceProcessor::class,
            structuredContent: true,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
        'hello_list_projects' => new McpTool(
            description: 'List CV projects (Flow, Uniflow, Hello, etc.).',
            processor: HelloListProjectsProcessor::class,
            structuredContent: true,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
        'hello_search_cv' => new McpTool(
            description: 'Deterministic substring search across experiences, projects, skills.',
            input: HelloCvSearchInput::class,
            processor: HelloSearchCvProcessor::class,
            structuredContent: true,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
        'hello_list_skills' => new McpTool(
            description: 'List skills sorted by category then name.',
            processor: HelloListSkillsProcessor::class,
            structuredContent: true,
            security: "is_granted('PUBLIC_ACCESS')",
        ),
    ],
)]
final class HelloCvMcp {}
