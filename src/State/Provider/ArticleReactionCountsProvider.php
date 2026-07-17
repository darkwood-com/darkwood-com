<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\ArticleReactionService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_sum;
use function is_string;

/**
 * @author Mathieu Ledru
 */
final readonly class ArticleReactionCountsProvider implements ProviderInterface
{
    public function __construct(
        private ArticleRepository $articles,
        private ArticleReactionService $reactions,
    ) {}

    /** @return array<string, mixed> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $article = $this->resolveArticle($uriVariables);
        if (!$article instanceof Article) {
            throw new NotFoundHttpException('Article not found.');
        }

        $summary = $this->reactions->getSummary($article, null);

        return [
            'generation_id' => $article->getGenerationId(),
            'article_id' => $article->getId(),
            'counts' => $summary['counts'],
            'total' => array_sum($summary['counts']),
        ];
    }

    /** @param array<string, mixed> $uriVariables */
    private function resolveArticle(array $uriVariables): ?Article
    {
        $generationId = $uriVariables['generationId'] ?? null;
        if (is_string($generationId) && '' !== trim($generationId)) {
            return $this->articles->findOneByGenerationId($generationId);
        }

        $slug = $uriVariables['slug'] ?? null;
        $locale = $uriVariables['locale'] ?? null;
        if (is_string($slug) && is_string($locale) && '' !== trim($slug) && '' !== trim($locale)) {
            return $this->articles->findOneBySlugAndLocale($slug, $locale);
        }

        return null;
    }
}
