<?php

declare(strict_types=1);

namespace App\Repository\Game;

use App\Entity\Game\Potion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PotionRepository.
 */
class PotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Potion::class);
    }

    public function findDefault()
    {
        $qb = $this->createQueryBuilder('p')->select('p')->addOrderBy('p.price', 'asc')->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findNext(Potion $potion)
    {
        $qb = $this->createQueryBuilder('p')->select('p')->addOrderBy('p.price', 'asc')->andWhere('p.price > :price')->setParameter('price', $potion->getPrice())->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPrevious(Potion $potion)
    {
        $qb = $this->createQueryBuilder('p')->select('p')->addOrderBy('p.price', 'desc')->andWhere('p.price < :price')->setParameter('price', $potion->getPrice())->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
