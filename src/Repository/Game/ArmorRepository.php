<?php

namespace App\Repository\Game;

use App\Entity\AppContent;
use App\Repository\BaseRepository;
use App\Entity\Game\Armor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ArmorRepository.
 */
class ArmorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Armor::class);
    }

    public function findDefault()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->addOrderBy('a.price', 'asc')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findNext(Armor $armor)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->addOrderBy('a.price', 'asc')
            ->andWhere('a.price > :price')->setParameter('price', $armor->getPrice())
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPrevious(Armor $armor)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->addOrderBy('a.price', 'desc')
            ->andWhere('a.price < :price')->setParameter('price', $armor->getPrice())
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
