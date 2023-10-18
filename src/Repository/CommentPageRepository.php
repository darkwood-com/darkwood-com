<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommentPage;
use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CommentPageRepository.
 */
class CommentPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentPage::class);
    }

    public function findActiveCommentByPageQuery(Page $page)
    {
        $qb = $this->createQueryBuilder('c')->select('c')->andWhere('c.active = true')->andWhere('c.page = :page')->setParameter('page', $page)->addOrderBy('c.created', 'desc');

        // $qb->getQuery()->useResultCache(true, 120, 'PageRepository::findOneToEdit'.($id ? 'id' : ''));
        return $qb->getQuery();
    }
}
