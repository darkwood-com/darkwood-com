<?php

namespace App\Repository\Game;

use App\Entity\Game\Sword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class SwordRepository.
 */
class SwordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sword::class);
    }

    public function findDefault()
    {
        $qb = $this->createQueryBuilder('s')->select('s')->addOrderBy('s.price', 'asc')->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findNext(Sword $sword)
    {
        $qb = $this->createQueryBuilder('a')->select('a')->addOrderBy('a.price', 'asc')->andWhere('a.price > :price')->setParameter('price', $sword->getPrice())->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPrevious(Sword $sword)
    {
        $qb = $this->createQueryBuilder('a')->select('a')->addOrderBy('a.price', 'desc')->andWhere('a.price < :price')->setParameter('price', $sword->getPrice())->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
