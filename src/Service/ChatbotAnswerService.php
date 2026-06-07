<?php

declare(strict_types=1);

namespace App\Service;

use function sprintf;

final readonly class ChatbotAnswerService
{
    public function __construct(private ArticleKnowledgeSearchService $knowledge) {}

    /**
     * @return array{answer:string,sources:list<int>,premium_context_used:bool}
     */
    public function answer(string $question, ?int $userId = null): array
    {
        $matches = $this->knowledge->search($question, $userId, 3);
        if ([] === $matches) {
            return [
                'answer' => "Je n'ai pas trouve de contenu pertinent dans la base Darkwood.",
                'sources' => [],
                'premium_context_used' => false,
            ];
        }

        $sources = array_map(static fn (array $m): int => (int) $m['article_id'], $matches);
        $lines = [];
        $premiumUsed = false;
        foreach ($matches as $index => $match) {
            $content = trim(strip_tags((string) $match['content']));
            if ((bool) $match['premium']) {
                $premiumUsed = true;
            }

            $lines[] = sprintf('%d) %s - %s', $index + 1, $match['title'], mb_substr($content, 0, 280));
        }

        return [
            'answer' => "Synthese Darkwood:\n" . implode("\n", $lines),
            'sources' => $sources,
            'premium_context_used' => $premiumUsed,
        ];
    }
}
