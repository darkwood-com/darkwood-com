<?php

namespace App\Repository\Game;

use App\Entity\Game\LevelUp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class LevelUpRepository.
 */
class LevelUpRepository extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Game\LevelUp::class);
    }
    public function findByXp($xp)
    {
        $qb = $this->createQueryBuilder('l')->select('l')->addOrderBy('l.xp', 'asc')->andWhere('l.xp > :xp')->setParameter('xp', $xp)->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
