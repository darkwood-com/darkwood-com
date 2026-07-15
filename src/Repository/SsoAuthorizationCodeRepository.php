<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SsoAuthorizationCode;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SsoAuthorizationCode>
 */
class SsoAuthorizationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SsoAuthorizationCode::class);
    }

    public function findOneValidForUpdate(string $code, DateTimeImmutable $now): ?SsoAuthorizationCode
    {
        return $this->createQueryBuilder('authorization_code')
            ->where('authorization_code.code = :code')
            ->andWhere('authorization_code.expiresAt > :now')
            ->setParameter('code', $code)
            ->setParameter('now', $now)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }
}
