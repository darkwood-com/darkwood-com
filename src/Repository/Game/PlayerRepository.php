<?php

namespace App\Repository\Game;

use App\Entity\Game\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class PlayerRepository.
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }
    public function findRand()
    {
        $qb = $this->createQueryBuilder('p')->select('p')->addSelect('RAND() as HIDDEN rand')->orderBy('rand')->andWhere('p.user IS NOT NULL')->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
    public function findActiveQuery($mode = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p')->andWhere('p.user IS NOT NULL');
        if (in_array($mode, ['by_class_human', 'by_class_lucky_lucke', 'by_class_panoramix', 'by_class_popeye'])) {
            $qb->addOrderBy('p.xp', 'desc');
            $qb->leftJoin('p.classe', 'c');
            if ($mode == 'by_class_human') {
                $qb->andWhere('c.title = :title')->setParameter('title', 'Humain');
            } elseif ($mode == 'by_class_popeye') {
                $qb->andWhere('c.title = :title')->setParameter('title', 'Popeye');
            } elseif ($mode == 'by_class_lucky_lucke') {
                $qb->andWhere('c.title = :title')->setParameter('title', 'Lucky luke');
            } elseif ($mode == 'by_class_panoramix') {
                $qb->andWhere('c.title = :title')->setParameter('title', 'Panoramix');
            }
        } elseif (in_array($mode, ['daily_fight_by_defeats', 'daily_fight_by_victories'])) {
            if ($mode == 'daily_fight_by_defeats') {
                $qb->addOrderBy('p.dailyBattleDefeats', 'desc');
            } elseif ($mode == 'daily_fight_by_victories') {
                $qb->addOrderBy('p.dailyBattleVictories', 'desc');
            }
        } else {
            $qb->addOrderBy('p.xp', 'desc');
        }
        return $qb->getQuery();
    }
}
