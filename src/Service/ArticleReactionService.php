<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticleReaction;
use App\Entity\User;
use App\Enum\ArticleReactionEmoji;
use App\Repository\ArticleReactionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ArticleReactionService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ArticleReactionRepository $articleReactionRepository,
    ) {}

    /**
     * @return array{counts: array<string, int>, userReactions: list<string>}
     */
    public function getSummary(Article $article, ?User $user): array
    {
        $summaries = $this->getSummariesForArticles([$article], $user);

        return $summaries[$article->getId()] ?? $this->emptySummary();
    }

    /**
     * @param list<Article> $articles
     *
     * @return array<int, array{counts: array<string, int>, userReactions: list<string>}>
     */
    public function getSummariesForArticles(array $articles, ?User $user): array
    {
        $articleIds = [];
        foreach ($articles as $article) {
            $articleId = $article->getId();
            if (null !== $articleId) {
                $articleIds[] = $articleId;
            }
        }

        if ([] === $articleIds) {
            return [];
        }

        $countsByArticle = $this->articleReactionRepository->countByArticleIds($articleIds);
        $userReactionsByArticle = $user instanceof User
            ? $this->articleReactionRepository->findUserReactionsForArticles($articleIds, $user)
            : [];

        $summaries = [];
        foreach ($articleIds as $articleId) {
            $summaries[$articleId] = [
                'counts' => $countsByArticle[$articleId] ?? [],
                'userReactions' => $userReactionsByArticle[$articleId] ?? [],
            ];
        }

        return $summaries;
    }

    /**
     * @return array{counts: array<string, int>, userReactions: list<string>, active: bool}
     */
    public function addReaction(Article $article, ArticleReactionEmoji $emoji, User $user): array
    {
        $existingReaction = $this->articleReactionRepository->findOneByArticleUserAndEmoji(
            (int) $article->getId(),
            $user,
            $emoji,
        );

        if (!$existingReaction instanceof ArticleReaction) {
            $reaction = new ArticleReaction();
            $reaction->setArticle($article);
            $reaction->setUser($user);
            $reaction->setEmoji($emoji);
            $reaction->setCreated(new DateTime('now'));
            $reaction->setUpdated(new DateTime('now'));
            $this->em->persist($reaction);
            $this->em->flush();
        }

        $summary = $this->getSummary($article, $user);
        $summary['active'] = true;

        return $summary;
    }

    /**
     * @return array{counts: array<string, int>, userReactions: list<string>, active: bool}
     */
    public function toggleReaction(Article $article, ArticleReactionEmoji $emoji, User $user): array
    {
        $existingReaction = $this->articleReactionRepository->findOneByArticleUserAndEmoji(
            (int) $article->getId(),
            $user,
            $emoji,
        );

        if ($existingReaction instanceof ArticleReaction) {
            $this->em->remove($existingReaction);
        } else {
            $reaction = new ArticleReaction();
            $reaction->setArticle($article);
            $reaction->setUser($user);
            $reaction->setEmoji($emoji);
            $reaction->setCreated(new DateTime('now'));
            $reaction->setUpdated(new DateTime('now'));
            $this->em->persist($reaction);
        }

        $this->em->flush();

        $summary = $this->getSummary($article, $user);
        $summary['active'] = !$existingReaction instanceof ArticleReaction;

        return $summary;
    }

    /**
     * @return list<ArticleReactionEmoji>
     */
    public function getAvailableEmojis(): array
    {
        return ArticleReactionEmoji::all();
    }

    /**
     * @return array{counts: array<string, int>, userReactions: list<string>}
     */
    private function emptySummary(): array
    {
        return [
            'counts' => [],
            'userReactions' => [],
        ];
    }
}
