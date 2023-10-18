<?php

declare(strict_types=1);

namespace App\Repository\Game;

use App\Entity\Game\Gem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class GemRepository.
 */
class GemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gem::class);
    }

    public function findDefault()
    {
        $qb = $this->createQueryBuilder('g')->select('g')->addOrderBy('g.power', 'asc')->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOrdered()
    {
        $qb = $this->createQueryBuilder('g')->select('g')->addOrderBy('g.power', 'asc');
        $default = $this->findDefault();
        if ($default) {
            $qb->andWhere('g.id != :id')->setParameter('id', $default->getId());
        }

        return $qb->getQuery()->getResult();
    }
}
