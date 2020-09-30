<?php

namespace App\Repository;

use App\Entity\App;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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
     *
     * @return Query
     */
    public function queryForSearch($filters = [], $order = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
        ;

        if ($order == 'normal') {
            $qb->addOrderBy('a.created', 'desc');
        }

        if (count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                if ($key == 'limit_low') {
                    $qb->andWhere('a.created >= :low');
                    $qb->setParameter('low', $filter);
                    continue;
                }

                if ($key == 'limit_high') {
                    $qb->andWhere('a.created <= :high');
                    $qb->setParameter('high', $filter);
                    continue;
                }

                $qb->andWhere('a.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }

        //$qb->getQuery()->useResultCache(true, 120, 'AppRepository::queryForSearch');

        $query = $qb->getQuery();

        return $query;
    }

    /**
     * Find one for edit.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function findOneToEdit($id, $locale)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a', 'content')
            ->where('a.id = :id')
            ->addOrderBy('a.id', 'asc')
            ->setParameter('id', $id)
            ->leftJoin('a.contents', 'content', Query\Expr\Join::WITH, 'content.locale = :locale')
            ->setParameter('locale', $locale)
            ->addOrderBy('content.position')
        ;

        //$qb->getQuery()->useResultCache(true, 120, 'AppRepository::findOneToEdit'.($id ? 'id' : ''));

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one for edit.
     *
     * @param array $parameters
     *
     * @return mixed
     */
    public function findAll($parameters = [])
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
        ;

        //$qb->getQuery()->useResultCache(true, 120, 'AppRepository::findAll');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findActives($limit = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->addOrderBy('a.created', 'desc')
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        //$qb->getQuery()->useResultCache(true, 120, 'AppRepository::findActives');

        $query = $qb->getQuery();

        return new Paginator($query);
    }
}
