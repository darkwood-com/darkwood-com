<?php

namespace App\Repository\Game;

use App\Entity\Game\Enemy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class EnemyRepository.
 */
class EnemyRepository extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Game\Enemy::class);
    }
    public function findOrdered()
    {
        $qb = $this->createQueryBuilder('e')->select('e')->addOrderBy('e.xp', 'asc');
        return $qb->getQuery()->getResult();
    }
    public function findDefault()
    {
        $qb = $this->createQueryBuilder('e')->select('e')->addOrderBy('e.xp', 'asc')->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findNext(\App\Entity\Game\Enemy $enemy)
    {
        $qb = $this->createQueryBuilder('e')->select('e')->addOrderBy('e.xp', 'asc')->andWhere('e.xp > :xp')->setParameter('xp', $enemy->getXp())->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findPrevious(\App\Entity\Game\Enemy $enemy)
    {
        $qb = $this->createQueryBuilder('e')->select('e')->addOrderBy('e.xp', 'desc')->andWhere('e.xp < :xp')->setParameter('xp', $enemy->getXp())->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
