<?php

declare(strict_types=1);

namespace App\Service;

use App\ApiResource\HelloCvExperience;
use App\ApiResource\HelloCvProfile;
use App\ApiResource\HelloCvProject;
use App\ApiResource\HelloCvSkill;
use App\ApiResource\HelloCvSystem;

/**
 * Single source of truth for Hello CV data (API + MCP).
 *
 * Future: optional enrichment (AI or external sources) can wrap or decorate this service.
 */
final class HelloCvRepositoryService
{
    /** @var list<HelloCvExperience> */
    private array $experiences;

    /** @var list<HelloCvProject> */
    private array $projects;

    /** @var list<HelloCvSkill> */
    private array $skills;

    private HelloCvProfile $profile;

    public function __construct()
    {
        $this->profile = $this->buildProfile();
        $this->experiences = $this->buildExperiences();
        $this->projects = $this->buildProjects();
        $this->skills = $this->buildSkills();
    }

    public function getProfile(): HelloCvProfile
    {
        return $this->profile;
    }

    /**
     * @return list<HelloCvExperience>
     */
    public function getExperiences(): array
    {
        return $this->experiences;
    }

    /**
     * @return list<HelloCvProject>
     */
    public function getProjects(): array
    {
        return $this->projects;
    }

    /**
     * @return list<HelloCvSkill>
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    public function getExperienceById(string $id): ?HelloCvExperience
    {
        foreach ($this->experiences as $exp) {
            if ($exp->id === $id) {
                return $exp;
            }
        }

        return null;
    }

    public function getExperienceByCompany(string $company): ?HelloCvExperience
    {
        $needle = mb_strtolower($company);
        foreach ($this->experiences as $exp) {
            if (mb_strtolower($exp->company) === $needle) {
                return $exp;
            }
        }

        return null;
    }

    /**
     * @return array{query: string, experiences: list<HelloCvExperience>, projects: list<HelloCvProject>, skills: list<HelloCvSkill>}
     */
    public function searchCv(string $query): array
    {
        $q = mb_strtolower(trim($query));
        if ($q === '') {
            return [
                'query' => $query,
                'experiences' => [],
                'projects' => [],
                'skills' => [],
            ];
        }

        $experiences = [];
        foreach ($this->experiences as $exp) {
            if ($this->matchesExperience($exp, $q)) {
                $experiences[] = $exp;
            }
        }

        $projects = [];
        foreach ($this->projects as $proj) {
            if ($this->matchesProject($proj, $q)) {
                $projects[] = $proj;
            }
        }

        $skills = [];
        foreach ($this->skills as $skill) {
            if ($this->matchesSkill($skill, $q)) {
                $skills[] = $skill;
            }
        }

        return [
            'query' => $query,
            'experiences' => $experiences,
            'projects' => $projects,
            'skills' => $skills,
        ];
    }

    private function matchesExperience(HelloCvExperience $exp, string $q): bool
    {
        $haystacks = [
            $exp->company,
            $exp->role,
            $exp->description,
            ...$exp->stack,
            ...$exp->highlights,
        ];

        return $this->anyContains($haystacks, $q);
    }

    private function matchesProject(HelloCvProject $proj, string $q): bool
    {
        $haystacks = [
            $proj->name,
            $proj->description,
            $proj->type,
            ...$proj->tags,
        ];

        return $this->anyContains($haystacks, $q);
    }

    private function matchesSkill(HelloCvSkill $skill, string $q): bool
    {
        return str_contains(mb_strtolower($skill->name . ' ' . $skill->category), $q);
    }

    /**
     * @param list<string> $haystacks
     */
    private function anyContains(array $haystacks, string $q): bool
    {
        foreach ($haystacks as $text) {
            if ($text !== '' && str_contains(mb_strtolower($text), $q)) {
                return true;
            }
        }

        return false;
    }

    private function buildProfile(): HelloCvProfile
    {
        $systems = [
            new HelloCvSystem(
                id: 'flow',
                name: 'Flow',
                description: 'PHP orchestration: async composition, functional assembly of reusable components.',
                links: [['label' => 'GitHub', 'url' => 'https://github.com/darkwood-fr/flow']],
            ),
            new HelloCvSystem(
                id: 'uniflow',
                name: 'Uniflow',
                description: 'TypeScript orchestration layer connecting business tools.',
                links: [['label' => 'GitHub', 'url' => 'https://github.com/uniflow-io/uniflow']],
            ),
            new HelloCvSystem(
                id: 'darkwood',
                name: 'Darkwood',
                description: 'Automation products, APIs, and monetization experiments (Flow, Uniflow, API keys).',
                links: [['label' => 'GitHub org', 'url' => 'https://github.com/darkwood-com']],
            ),
        ];

        return new HelloCvProfile(
            name: 'Mathieu Ledru',
            role: 'Senior backend / fullstack engineer',
            summary: 'Ten+ years delivering PHP/Symfony backends, APIs, and data pipelines in high-traffic and regulated contexts. Focus on API design, DDD, CI/CD, containers, observability, and deterministic automation. Work on orchestration systems (Flow, Uniflow) and pragmatic LLM/MCP integration in maintainable architectures.',
            location: 'Brussels, Belgium',
            links: [
                ['label' => 'Freelance (Hello)', 'url' => 'https://hello.darkwood.com'],
                ['label' => 'Blog', 'url' => 'https://blog.darkwood.com'],
                ['label' => 'GitHub', 'url' => 'https://github.com/darkwood-com'],
                ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/mathieu-ledru'],
                ['label' => 'Twitter', 'url' => 'https://twitter.com/matyo91'],
                ['label' => 'Discord', 'url' => 'https://discord.com/invite/tMDCF8RyvE'],
                ['label' => 'YouTube', 'url' => 'https://www.youtube.com/@matyo91'],
                ['label' => 'YouTube (DJ)', 'url' => 'https://www.youtube.com/@djmatyo91'],
            ],
            systems: $systems,
        );
    }

    /**
     * @return list<HelloCvExperience>
     */
    private function buildExperiences(): array
    {
        return [
            new HelloCvExperience(
                id: 'eeas-2025-2026',
                company: 'EEAS',
                role: 'Senior Symfony / React engineer',
                startDate: '2025-06',
                endDate: null,
                description: 'Internal tooling under strict confidentiality in an institutional context (Brussels).',
                stack: ['Symfony', 'React.js', 'PHP'],
                highlights: [
                    'Sensitive-scope internal application delivery',
                    'Symfony and React stack',
                ],
            ),
            new HelloCvExperience(
                id: 'twenga-2024-2025',
                company: 'Twenga Solutions',
                role: 'Senior Symfony engineer / tech lead',
                startDate: '2024-04',
                endDate: '2025-05',
                description: 'Multi-country price comparison platform refactor at scale (traffic, acquisition, data).',
                stack: ['Symfony', 'Laravel', 'Node.js', 'React', 'PHP 8.1', 'TypeScript', 'GCP', 'Firestore', 'BigQuery', 'MySQL', 'Shopware', 'PrestaShop', 'Shopify'],
                highlights: [
                    'DDD refactor for scalability and domain/data split',
                    'Acquisition and traffic processing across seven countries',
                    'Google Cloud and Retail-related data tooling',
                ],
            ),
            new HelloCvExperience(
                id: 'gamestream-2023-2024',
                company: 'Gamestream',
                role: 'Senior Symfony engineer (backend)',
                startDate: '2023-05',
                endDate: '2024-04',
                description: 'High-availability streaming platform; progressive migration toward DDD; critical client environments.',
                stack: ['PHP 7.3', 'Symfony 5', 'Docker', 'Nginx', 'MySQL', 'Galera', 'PostgreSQL', 'PGPool', 'ProxySQL', 'MaxScale', 'HAProxy', 'GlusterFS', 'GitLab CI/CD', 'Redis', 'KeyDB'],
                highlights: [
                    'Maintenance and continuous deployment for critical customer stacks',
                    'Gradual PHP 7.3 / Symfony 5 migration with DDD',
                    'Incident reduction and production stability',
                ],
            ),
            new HelloCvExperience(
                id: 'claranet-2023',
                company: 'Claranet',
                role: 'Senior Symfony engineer',
                startDate: '2023-01',
                endDate: '2023-04',
                description: 'SKEMA competition portal backend: registration peaks, candidate journeys, scoring.',
                stack: ['PHP 8', 'Symfony 6', 'API Platform', 'PostgreSQL', 'ElasticSearch', 'RabbitMQ', 'Redis', 'Docker', 'Varnish'],
                highlights: [
                    'DDD, TDD, BDD on Symfony 6 / API Platform',
                    'Stable backoffice under registration spikes',
                ],
            ),
            new HelloCvExperience(
                id: 'mediatech-2022',
                company: 'Mediatech',
                role: 'Senior Symfony engineer',
                startDate: '2022-06',
                endDate: '2022-12',
                description: 'B2B video platform for large accounts; live stream hardening; Symfony 5 to 6 migration.',
                stack: ['Symfony 6', 'PHP 8.1', 'PostgreSQL', 'ElasticSearch', 'Docker', 'Angular', 'GCP', 'PHPUnit', 'Rx.js'],
                highlights: [
                    'Clients include Crédit Agricole, Renault, Alstom, Eiffage, Orano, Mémorial de la Shoah',
                    'Guzzle to Symfony HTTP migration; Wowza-backed live security work',
                ],
            ),
            new HelloCvExperience(
                id: 'darkwood-2020-2022',
                company: 'Darkwood',
                role: 'Founder / entrepreneur',
                startDate: '2020-03',
                endDate: '2022-05',
                description: 'Product company: automation tools (Flow, Uniflow), API-first control of complex logic, API-key monetization (freemium/premium), quotas, rate limiting, error normalization.',
                stack: ['PHP', 'TypeScript', 'Symfony', 'Node.js', 'Docker', 'React', 'GatsbyJS', 'GitHub Actions', 'Jest', 'MySQL', 'Twitter Bootstrap', 'Git'],
                highlights: [
                    'Flow: async modular PHP orchestration',
                    'Uniflow: TypeScript tool orchestration',
                    'Hexagonal/clean architecture for APIs',
                ],
            ),
            new HelloCvExperience(
                id: 'meero-2019-2020',
                company: 'Meero',
                role: 'Senior Symfony engineer (backend)',
                startDate: '2019-08',
                endDate: '2020-02',
                description: 'MyMeero mobile launch: API Platform REST API for photographers; agile squad practices.',
                stack: ['Symfony 4/5', 'PHP 7.4', 'API Platform', 'MySQL', 'Docker', 'PHPUnit'],
                highlights: [
                    'API Platform REST design for mobile app',
                    'DDD hexagonal architecture with TDD/BDD',
                ],
            ),
            new HelloCvExperience(
                id: 'bigyouth-2012-2019',
                company: 'BigYouth',
                role: 'Symfony engineer (fullstack)',
                startDate: '2012-10',
                endDate: '2019-07',
                description: 'Agency delivery: 15+ web projects for brands and enterprises; Symfony 2–4 backends; React/Angular front; Algolia/Elasticsearch search.',
                stack: ['PHP 5–7', 'Symfony', 'React.js', 'Angular', 'Elasticsearch', 'Algolia', 'Redis', 'Docker', 'HAProxy', 'Varnish', 'Git'],
                highlights: [
                    'Clients include Société Générale 150th, Ricard, Kronenbourg, NRJ Games, Primonial, Monabanq',
                    'Multilingual institutional, e-commerce, and showcase sites',
                ],
            ),
            new HelloCvExperience(
                id: 'les-argonautes-2008-2012',
                company: 'Les-Argonautes',
                role: 'PHP engineer (fullstack)',
                startDate: '2008-09',
                endDate: '2012-09',
                description: 'Agency: internal CRM/CMS, Peugeot/Roquette/ISS/Véolia projects; hybrid mobile (Sencha Touch, PhoneGap).',
                stack: ['PHP 5–7', 'JavaScript', 'jQuery', 'Sencha Touch', 'PhoneGap', 'Vagrant', 'Git'],
                highlights: [
                    'Custom CMS/backoffice performance and SEO',
                    'Hybrid iOS apps with offline sync',
                ],
            ),
            new HelloCvExperience(
                id: 'kylotonn-2008-intern',
                company: 'Kylotonn Entertainment',
                role: 'C++ graphics intern',
                startDate: '2008-03',
                endDate: '2008-08',
                description: 'Proprietary game engine: real-time rendering, depth of field, bloom; HLSL/DirectX.',
                stack: ['C++', 'HLSL', 'DirectX', 'Visual Studio', 'Subversion'],
                highlights: [
                    '3D engine rendering features for in-game integration',
                ],
            ),
            new HelloCvExperience(
                id: 'anfr-2007-intern',
                company: 'ANFR',
                role: 'VB.NET intern',
                startDate: '2007-05',
                endDate: '2007-08',
                description: 'HF radio-frequency mapping tool: specification, database model, full implementation.',
                stack: ['VB.NET', 'Microsoft Access', 'Visual Studio', 'Subversion'],
                highlights: [
                    'Regulatory spectrum mapping workflow',
                ],
            ),
        ];
    }

    /**
     * Homepage showcase order and assets mirror the historical Hello landing (pre–API cards).
     * `imageAsset` is a public path under `/public` for LiipImagine (`hello_blog` filter in Twig).
     *
     * @return list<HelloCvProject>
     */
    private function buildProjects(): array
    {
        return [
            new HelloCvProject(
                id: 'uniflow',
                name: 'Uniflow',
                description: 'Automate your recurring tasks',
                type: 'product',
                links: [['label' => 'Site', 'url' => 'https://uniflow.io']],
                tags: ['TypeScript', 'automation', 'orchestration'],
                imageAsset: '/hello/projects/uniflow.png',
            ),
            new HelloCvProject(
                id: 'bonzai',
                name: 'Matyo91 Bonzai',
                description: 'See my creator page on Bonzai',
                type: 'profile',
                links: [['label' => 'Bonzai', 'url' => 'https://www.bonzai.pro/matyo91']],
                tags: ['creator', 'Bonzai'],
                imageAsset: '/hello/projects/bonzai.png',
            ),
            new HelloCvProject(
                id: 'flow',
                name: 'Flow',
                description: 'Asynchronous Functional Programming',
                type: 'open-source',
                links: [['label' => 'Flow', 'url' => 'https://flow.darkwood.com']],
                tags: ['PHP', 'async', 'FBP'],
                imageAsset: '/hello/projects/flow.png',
            ),
            new HelloCvProject(
                id: 'darkwaar',
                name: 'Darkwaar',
                description: 'Will you be the darkest one at waar?',
                type: 'game',
                links: [['label' => 'Play', 'url' => 'https://darkwaar.com']],
                tags: ['game', 'multiplayer'],
                imageAsset: '/hello/projects/darkwaar.png',
            ),
            new HelloCvProject(
                id: 'wysiwyl',
                name: 'wysiwyl',
                description: 'What you see is what you like',
                type: 'open-source',
                links: [['label' => 'GitHub', 'url' => 'https://github.com/darkwood-com/wysiwyl']],
                tags: ['experiment', 'UI'],
                imageAsset: '/hello/projects/wysiwyl.png',
            ),
            new HelloCvProject(
                id: 'djstream',
                name: 'DJ Stream',
                description: 'DJ setup for streaming online',
                type: 'open-source',
                links: [['label' => 'GitHub', 'url' => 'https://github.com/darkwood-com/dj-stream']],
                tags: ['DJ', 'streaming'],
                imageAsset: '/hello/projects/dj-stream.png',
            ),
            new HelloCvProject(
                id: 'youtube',
                name: 'Matyo91 Youtube',
                description: 'My YouTube channel about tech and games',
                type: 'channel',
                links: [['label' => 'YouTube', 'url' => 'https://www.youtube.com/@matyo91']],
                tags: ['YouTube', 'tech'],
                imageAsset: '/hello/projects/youtube.png',
            ),
            new HelloCvProject(
                id: 'speakerdeck',
                name: 'Speakerdeck',
                description: 'My talks and slides about tech',
                type: 'speaking',
                links: [['label' => 'Speaker Deck', 'url' => 'https://speakerdeck.com/matyo91']],
                tags: ['talks', 'slides'],
                imageAsset: '/hello/projects/speakerdeck.png',
            ),
            new HelloCvProject(
                id: 'djmatyo91',
                name: 'DJMatyo91 Youtube',
                description: 'My DJ YouTube channel about Happy Hardcore',
                type: 'channel',
                links: [['label' => 'YouTube', 'url' => 'https://www.youtube.com/@djmatyo91']],
                tags: ['DJ', 'music'],
                imageAsset: '/hello/projects/djmatyo91.png',
            ),
        ];
    }

    /**
     * @return list<HelloCvSkill>
     */
    private function buildSkills(): array
    {
        $rows = [
            // Backend & PHP
            ['php', 'PHP 8', 'Backend'],
            ['symfony', 'Symfony', 'Backend'],
            ['api-platform', 'API Platform', 'Backend'],
            ['laravel', 'Laravel', 'Backend'],
            ['ddd', 'DDD', 'Architecture'],
            ['rest', 'REST APIs', 'APIs'],
            // Languages
            ['typescript', 'TypeScript', 'Languages'],
            ['javascript', 'JavaScript', 'Languages'],
            ['cpp', 'C++', 'Languages'],
            ['vbnet', 'VB.NET', 'Languages'],
            // Frontend
            ['react', 'React.js', 'Frontend'],
            ['angular', 'Angular', 'Frontend'],
            // Data
            ['postgresql', 'PostgreSQL', 'Data'],
            ['mysql', 'MySQL', 'Data'],
            ['elasticsearch', 'Elasticsearch', 'Data'],
            ['algolia', 'Algolia', 'Data'],
            ['redis', 'Redis / KeyDB', 'Data'],
            // Infra
            ['docker', 'Docker', 'Infrastructure'],
            ['nginx', 'Nginx', 'Infrastructure'],
            ['caddy', 'Caddy', 'Infrastructure'],
            ['varnish', 'Varnish', 'Infrastructure'],
            ['haproxy', 'HAProxy', 'Infrastructure'],
            ['gcp', 'Google Cloud Platform', 'Cloud'],
            ['rabbitmq', 'RabbitMQ', 'Messaging'],
            ['gitlab-ci', 'GitLab CI', 'CI/CD'],
            ['github-actions', 'GitHub Actions', 'CI/CD'],
            // Quality
            ['phpunit', 'PHPUnit', 'Quality'],
            ['tdd', 'TDD', 'Quality'],
            ['bdd', 'BDD', 'Quality'],
            // AI / integration
            ['mcp', 'MCP', 'AI & automation'],
            ['llm', 'LLM integration', 'AI & automation'],
            ['openapi', 'OpenAI API', 'AI & automation'],
            ['ollama', 'Ollama', 'AI & automation'],
        ];

        $skills = [];
        foreach ($rows as $row) {
            $skills[] = new HelloCvSkill(id: $row[0], name: $row[1], category: $row[2]);
        }

        return $skills;
    }
}
