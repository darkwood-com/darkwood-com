<?php

declare(strict_types=1);

namespace App\Repository\Game;

use App\Entity\Game\DailyBattle;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class DailyBattleRepository.
 */
class DailyBattleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyBattle::class);
    }

    /**
     * @param DateTime $date
     *
     * @throws NonUniqueResultException
     */
    public function findDaily($date): mixed
    {
        $beginDate = clone $date;
        $beginDate->setTime(0, 0, 0);

        $endDate = clone $date;
        $endDate->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('db')->select('db')->addOrderBy('db.created', 'asc')->andWhere('db.status = :status')->setParameter('status', DailyBattle::STATUS_DAILY_USER)->andWhere('db.created >= :begin')->setParameter('begin', $beginDate)->andWhere('db.created <= :end')->setParameter('end', $endDate)->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param DateTime $date
     */
    public function findDailyBattles($date): array
    {
        $beginDate = clone $date;
        $beginDate->setTime(0, 0, 0);

        $endDate = clone $date;
        $endDate->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('db')->select('db')->addOrderBy('db.created', 'asc')->andWhere('db.created >= :begin')->setParameter('begin', $beginDate)->andWhere('db.created <= :end')->setParameter('end', $endDate);
        $qb->andWhere($qb->expr()->in('db.status', [DailyBattle::STATUS_NEW_WIN, DailyBattle::STATUS_NEW_LOSE]));

        return $qb->getQuery()->getResult();
    }
}
