<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\CommentArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CommentArticleRepository.
 */
class CommentArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentArticle::class);
    }

    public function findActiveCommentByArticleQuery(Article $article)
    {
        $qb = $this->createQueryBuilder('c')->select('c')->andWhere('c.active = true')->andWhere('c.article = :article')->setParameter('article', $article)->addOrderBy('c.created', 'desc');

        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findOneToEdit'.($id ? 'id' : ''));
        return $qb->getQuery();
    }
}
