<?php

declare(strict_types=1);

namespace App\Service;

use App\ApiResource\HelloCvExperience;
use App\ApiResource\HelloCvProject;
use App\ApiResource\HelloCvSkill;

/**
 * Maps Hello CV repository data into a single view model for the Hello homepage.
 */
final readonly class HelloHomepageDataFactoryService
{
    /** @var list<string> */
    private const WORK_EXPERIENCE_IDS = [
        'eeas-2025-2026',
        'twenga-2024-2025',
        'gamestream-2023-2024',
        'mediatech-2022',
        'bigyouth-2012-2019',
    ];

    private const SKILLS_MAX = 18;

    public function __construct(
        private HelloCvRepositoryService $helloCvRepository,
    ) {}

    /**
     * @return array{
     *   profile: array{name: string, role: string, summary: string, location: string, hero_links: list<array{label: string, url: string}>},
     *   work: list<array{id: string, company: string, role: string, period: string, description: string, highlights: list<string>}>,
     *   skills: list<array{name: string, category: string}>,
     *   projects: list<array{id: string, name: string, description: string, type: string, primary_url: string|null, tags: list<string>, image_asset: string|null}>
     * }
     */
    public function build(): array
    {
        $p = $this->helloCvRepository->getProfile();

        return [
            'profile' => [
                'name' => $p->name,
                'role' => $p->role,
                'summary' => $p->summary,
                'location' => $p->location,
                'hero_links' => $this->filterHeroLinks($p->links),
            ],
            'work' => $this->selectWork(),
            'skills' => $this->selectSkills(),
            'projects' => $this->selectProjects(),
        ];
    }

    /**
     * @param list<array{label: string, url: string}> $links
     *
     * @return list<array{label: string, url: string}>
     */
    private function filterHeroLinks(array $links): array
    {
        $out = [];
        foreach ($links as $link) {
            if (str_contains(mb_strtolower($link['label']), 'freelance')) {
                continue;
            }
            $out[] = $link;
            if (\count($out) >= 8) {
                break;
            }
        }

        return $out;
    }

    /**
     * @return list<array{id: string, company: string, role: string, period: string, description: string, highlights: list<string>}>
     */
    private function selectWork(): array
    {
        $byId = [];
        foreach ($this->helloCvRepository->getExperiences() as $exp) {
            $byId[$exp->id] = $exp;
        }

        $selected = [];
        foreach (self::WORK_EXPERIENCE_IDS as $id) {
            if (isset($byId[$id])) {
                $selected[] = $this->mapExperience($byId[$id]);
            }
        }

        return $selected;
    }

    private function mapExperience(HelloCvExperience $exp): array
    {
        $end = $exp->endDate ?? 'present';

        return [
            'id' => $exp->id,
            'company' => $exp->company,
            'role' => $exp->role,
            'period' => $exp->startDate.' — '.$end,
            'description' => $exp->description,
            'highlights' => \array_slice($exp->highlights, 0, 4),
        ];
    }

    /**
     * @return list<array{name: string, category: string}>
     */
    private function selectSkills(): array
    {
        $skills = $this->helloCvRepository->getSkills();
        usort($skills, static function (HelloCvSkill $a, HelloCvSkill $b): int {
            $c = strcmp($a->category, $b->category);

            return $c !== 0 ? $c : strcmp($a->name, $b->name);
        });

        $out = [];
        foreach (\array_slice($skills, 0, self::SKILLS_MAX) as $skill) {
            $out[] = ['name' => $skill->name, 'category' => $skill->category];
        }

        return $out;
    }

    /**
     * @return list<array{id: string, name: string, description: string, type: string, primary_url: string|null, tags: list<string>, image_asset: string|null}>
     */
    private function selectProjects(): array
    {
        $out = [];
        foreach ($this->helloCvRepository->getProjects() as $proj) {
            $out[] = $this->mapProject($proj);
        }

        return $out;
    }

    private function mapProject(HelloCvProject $proj): array
    {
        $primary = null;
        if ($proj->links !== []) {
            $primary = $proj->links[0]['url'] ?? null;
        }

        return [
            'id' => $proj->id,
            'name' => $proj->name,
            'description' => $proj->description,
            'type' => $proj->type,
            'primary_url' => $primary,
            'tags' => \array_slice($proj->tags, 0, 6),
            'image_asset' => $proj->imageAsset,
        ];
    }
}
