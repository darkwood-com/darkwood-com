<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Enum\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\EntitlementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use function count;

final readonly class ArticleKnowledgeSearchService
{
    public function __construct(
        private ArticleRepository $articles,
        private UserRepository $users,
        private EntitlementRepository $entitlements,
    ) {}

    /**
     * @return list<array{article_id:int,title:string,content:string,premium:bool}>
     */
    public function search(string $query, ?int $userId = null, int $limit = 5, string $locale = 'en'): array
    {
        $query = trim($query);
        if ('' === $query) {
            return [];
        }

        $canReadPremium = $this->isPremiumUser($userId);
        $qb = $this->articles->findActivesQueryBuilder($locale, max(1, min(50, $limit * 4)));
        $paginator = new Paginator($qb->getQuery());
        $result = [];
        foreach ($paginator as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $translation = $article->getOneTranslation($locale);
            $title = (string) $translation->getTitle();
            $content = $article->getType() === ArticleType::Auto
                ? ($article->isPremium() && !$canReadPremium ? '' : (string) ($translation->getPremiumContent() ?? $translation->getContent() ?? ''))
                : (string) ($translation->getContent() ?? '');

            $haystack = mb_strtolower($title . ' ' . strip_tags($content));
            if (!str_contains($haystack, mb_strtolower($query))) {
                continue;
            }

            $result[] = [
                'article_id' => (int) $article->getId(),
                'title' => $title,
                'content' => $content,
                'premium' => $article->isPremium(),
            ];
            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }

    private function isPremiumUser(?int $userId): bool
    {
        if (null === $userId) {
            return false;
        }

        $user = $this->users->find($userId);
        if (null === $user) {
            return false;
        }

        return $user->isPremium() || $this->entitlements->findActivePremiumForUser($user) instanceof \App\Entity\Entitlement;
    }
}
