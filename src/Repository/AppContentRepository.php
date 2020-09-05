<?php

namespace App\Repository;

use App\Entity\AppContent;
use App\Repository\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AppContentRepository.
 */
class AppContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppContent::class);
    }

    /**
     * Get all user query, using for paginatioac.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function queryForSearch($filters = array(), $order = null)
    {
        $qb = $this->createQueryBuilder('ac')
            ->select('ac')
        ;

        if ($order == 'normal') {
            $qb->addOrderBy('ac.created', 'desc');
        }

        if (count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                if ($key == 'limit_low') {
                    $qb->andWhere('ac.created >= :low');
                    $qb->setParameter('low', $filter);
                    continue;
                }

                if ($key == 'limit_high') {
                    $qb->andWhere('ac.created <= :high');
                    $qb->setParameter('high', $filter);
                    continue;
                }

                $qb->andWhere('ac.'.$key.' LIKE :'.$key);
                $qb->setParameter($key, '%'.$filter.'%');
            }
        }

        #$qb->getQuery()->useResultCache(true, 120, 'AppContentRepository::queryForSearch');

        $query = $qb->getQuery();

        return $query;
    }

    /**
     * Find one for edit.
     *
     * @param $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        $qb = $this->createQueryBuilder('ac')
            ->select('ac')
            ->where('ac.id = :id')
            ->orderBy('ac.id', 'asc')
            ->setParameter('id', $id);

        #$qb->getQuery()->useResultCache(true, 120, 'AppContentRepository::findOneToEdit'.($id ? 'id' : ''));

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one for edit.
     *
     * @param $id
     *
     * @return mixed
     */
    public function findAll($parameters = array())
    {
        $qb = $this->createQueryBuilder('ac')
            ->select('ac')
        ;

        #$qb->getQuery()->useResultCache(true, 120, 'AppContentRepository::findAll');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findActives($limit = null)
    {
        $qb = $this->createQueryBuilder('ac')
            ->select('ac')
            ->addOrderBy('ac.created', 'desc')
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        #$qb->getQuery()->useResultCache(true, 120, 'AppContentRepository::findActives');

        $query = $qb->getQuery();

        return new Paginator($query);
    }
}
