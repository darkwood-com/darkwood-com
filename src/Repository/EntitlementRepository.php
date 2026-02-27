<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entitlement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entitlement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entitlement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entitlement[]    findAll()
 * @method Entitlement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntitlementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entitlement::class);
    }

    public function findActivePremiumForUser(User $user): ?Entitlement
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.plan = :plan')
            ->andWhere('e.active = :active')
            ->andWhere('e.validUntil IS NULL OR e.validUntil > :now')
            ->setParameter('user', $user)
            ->setParameter('plan', Entitlement::PLAN_PREMIUM)
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('e.validUntil', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
