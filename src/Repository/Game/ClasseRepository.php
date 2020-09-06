<?php

namespace App\Repository\Game;

use App\Entity\Game\Classe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ClasseRepository.
 */
class ClasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classe::class);
    }

    public function findDefault()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->addOrderBy('c.strength', 'asc')
            ->addOrderBy('c.dexterity', 'asc')
            ->addOrderBy('c.vitality', 'asc')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findList()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
        ;

        $default = $this->findDefault();
        if ($default) {
            $qb->andWhere('c.id != :id')->setParameter('id', $default->getId());
        }

        return $qb->getQuery()->getResult();
    }
}
