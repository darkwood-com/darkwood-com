<?php

namespace App\Repository\Game;

use App\Entity\Game\Armor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class ArmorRepository.
 */
class ArmorRepository extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Game\Armor::class);
    }
    public function findDefault()
    {
        $qb = $this->createQueryBuilder('a')->select('a')->addOrderBy('a.price', 'asc')->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findNext(\App\Entity\Game\Armor $armor)
    {
        $qb = $this->createQueryBuilder('a')->select('a')->addOrderBy('a.price', 'asc')->andWhere('a.price > :price')->setParameter('price', $armor->getPrice())->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findPrevious(\App\Entity\Game\Armor $armor)
    {
        $qb = $this->createQueryBuilder('a')->select('a')->addOrderBy('a.price', 'desc')->andWhere('a.price < :price')->setParameter('price', $armor->getPrice())->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
