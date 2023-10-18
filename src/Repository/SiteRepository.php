<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class SiteRepository.
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function queryForSearch($filters = [])
    {
        $qb = $this->createQueryBuilder('s')->select('s')->orderBy('s.id', 'asc');
        if ($filters !== []) {
            foreach ($filters as $key => $filter) {
                $qb->andWhere('s.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }

        return $qb->getQuery();
    }

    /**
     * Find one for edit profile.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        $qb = $this->createQueryBuilder('s')->select('s')->where('s.id = :id')->setParameter('id', $id);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one by host.
     *
     * @param string $host
     *
     * @return mixed
     */
    public function findOneByHost($host)
    {
        $qb = $this->createQueryBuilder('s')->select('s')->where('s.host = :host')->setParameter('host', $host);
        $query = $qb->getQuery();

        // $query->useResultCache(true, 120, 'SiteRepository::findOneByHost' . $host);
        return $query->getOneOrNullResult();
    }

    /**
     * Find one by ref.
     *
     * @param string $ref
     *
     * @return mixed
     */
    public function findOneByRef($ref)
    {
        $qb = $this->createQueryBuilder('s')->select('s')->where('s.ref = :ref')->setParameter('ref', $ref);
        $query = $qb->getQuery();

        // $query->useResultCache(true, 120, 'SiteRepository::findOneByRef' . $ref);
        return $query->getOneOrNullResult();
    }

    public function findAll($parameters = [])
    {
        $qb = $this->createQueryBuilder('s')->select('s')->orderBy('s.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function findActives()
    {
        $qb = $this->createQueryBuilder('s')->select('s')->andWhere('s.active = true')->orderBy('s.position', 'asc');

        return $qb->getQuery()->getResult();
    }
}
