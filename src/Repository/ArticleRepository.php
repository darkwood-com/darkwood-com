<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ArticleRepository.
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     */
    public function queryForSearch($filters = [], $locale = 'en', $order = null): Query
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        if ($order === 'normal') {
            $qb->addOrderBy('n.created', 'desc');
        }

        if ($filters !== []) {
            foreach ($filters as $key => $filter) {
                if ($key === 'limit_low') {
                    $qb->andWhere('n.created >= :low');
                    $qb->setParameter('low', $filter);

                    continue;
                }

                if ($key === 'limit_high') {
                    $qb->andWhere('n.created <= :high');
                    $qb->setParameter('high', $filter);

                    continue;
                }

                $qb->andWhere('n.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }

        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::queryForSearch');
        return $qb->getQuery();
    }

    /**
     * Find one for edit.
     *
     * @param int $id
     */
    public function findOneToEdit($id): mixed
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->where('n.id = :id')->orderBy('n.id', 'asc')->setParameter('id', $id);
        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findOneBySlug($slug, $locale)
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->andWhere('nts.slug = :slug')->setParameter('slug', $slug)->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one for edit.
     *
     * @param array $parameters
     */
    public function findAll($parameters = []): array
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->addOrderBy('n.created', 'desc');
        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findAll');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findActivesQueryBuilder($locale = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->addOrderBy('n.created', 'desc')->andWhere('nts.active = true');
        if ($locale) {
            $qb->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function findActives($locale = null, $limit = null): Paginator
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->addOrderBy('n.created', 'desc')->andWhere('nts.active = true');
        if ($locale) {
            $qb->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findActives');
        $query = $qb->getQuery();

        return new Paginator($query);
    }
}
