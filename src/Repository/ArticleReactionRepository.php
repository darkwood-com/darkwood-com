<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArticleReaction;
use App\Entity\User;
use App\Enum\ArticleReactionEmoji;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleReaction>
 */
class ArticleReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleReaction::class);
    }

    /**
     * @param list<int> $articleIds
     *
     * @return array<int, array<string, int>>
     */
    public function countByArticleIds(array $articleIds): array
    {
        if ([] === $articleIds) {
            return [];
        }

        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.article) AS articleId', 'r.emoji AS emoji', 'COUNT(r.id) AS reactionCount')
            ->andWhere('r.article IN (:articleIds)')
            ->setParameter('articleIds', $articleIds)
            ->groupBy('r.article', 'r.emoji')
            ->getQuery()
            ->getArrayResult()
        ;

        $counts = [];
        foreach ($rows as $row) {
            $articleId = (int) $row['articleId'];
            $emoji = $row['emoji'] instanceof ArticleReactionEmoji ? $row['emoji']->value : (string) $row['emoji'];
            $counts[$articleId][$emoji] = (int) $row['reactionCount'];
        }

        return $counts;
    }

    /**
     * @param list<int> $articleIds
     *
     * @return array<int, list<string>>
     */
    public function findUserReactionsForArticles(array $articleIds, User $user): array
    {
        if ([] === $articleIds) {
            return [];
        }

        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.article) AS articleId', 'r.emoji AS emoji')
            ->andWhere('r.article IN (:articleIds)')
            ->andWhere('r.user = :user')
            ->setParameter('articleIds', $articleIds)
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult()
        ;

        $reactions = [];
        foreach ($rows as $row) {
            $articleId = (int) $row['articleId'];
            $emoji = $row['emoji'] instanceof ArticleReactionEmoji ? $row['emoji']->value : (string) $row['emoji'];
            $reactions[$articleId][] = $emoji;
        }

        return $reactions;
    }

    public function findOneByArticleUserAndEmoji(int $articleId, User $user, ArticleReactionEmoji $emoji): ?ArticleReaction
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.article = :articleId')
            ->andWhere('r.user = :user')
            ->andWhere('r.emoji = :emoji')
            ->setParameter('articleId', $articleId)
            ->setParameter('user', $user)
            ->setParameter('emoji', $emoji)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
