<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\App;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AppRepository.
 */
class AppRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, App::class);
    }

    /**
     * Get all user query, using for paginatioa.
     *
     * @param array $filters
     */
    public function queryForSearch($filters = [], $order = null): Query
    {
        $qb = $this->createQueryBuilder('a')->select('a');
        if ($order === 'normal') {
            $qb->addOrderBy('a.created', 'desc');
        }

        if ($filters !== []) {
            foreach ($filters as $key => $filter) {
                if ($key === 'limit_low') {
                    $qb->andWhere('a.created >= :low');
                    $qb->setParameter('low', $filter);

                    continue;
                }

                if ($key === 'limit_high') {
                    $qb->andWhere('a.created <= :high');
                    $qb->setParameter('high', $filter);

                    continue;
                }

                $qb->andWhere('a.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }

        // $qb->getQuery()->useResultCache(true, 120, 'AppRepository::queryForSearch');
        return $qb->getQuery();
    }

    /**
     * Find one for edit.
     *
     * @param int $id
     */
    public function findOneToEdit($id, $locale): mixed
    {
        $qb = $this->createQueryBuilder('a')->select('a', 'content')->where('a.id = :id')->addOrderBy('a.id', 'asc')->setParameter('id', $id)->leftJoin('a.contents', 'content', Join::WITH, 'content.locale = :locale')->setParameter('locale', $locale)->addOrderBy('content.position');
        // $qb->getQuery()->useResultCache(true, 120, 'AppRepository::findOneToEdit'.($id ? 'id' : ''));
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
        $qb = $this->createQueryBuilder('a')->select('a');
        // $qb->getQuery()->useResultCache(true, 120, 'AppRepository::findAll');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findActives($limit = null): Paginator
    {
        $qb = $this->createQueryBuilder('a')->select('a')->addOrderBy('a.created', 'desc');
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        // $qb->getQuery()->useResultCache(true, 120, 'AppRepository::findActives');
        $query = $qb->getQuery();

        return new Paginator($query);
    }
}
