<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiKey;
use App\Entity\ApiKeyUsage;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function sprintf;

final class ApiKeyUsageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKeyUsage::class);
    }

    public function incrementIfBelowLimit(ApiKey $apiKey, DateTimeImmutable $date, int $limit): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $table = $this->getClassMetadata()->getTableName();
        $dateValue = $date->format('Y-m-d');

        // Ensure row exists, then atomically increment only if still under limit.
        $connection->executeStatement(
            sprintf(
                'INSERT IGNORE INTO %s (api_key_id, usage_date, usage_count) VALUES (:apiKeyId, :usageDate, 0)',
                $table
            ),
            [
                'apiKeyId' => $apiKey->getId(),
                'usageDate' => $dateValue,
            ]
        );

        $updated = $connection->executeStatement(
            sprintf(
                'UPDATE %s
                 SET usage_count = usage_count + 1
                 WHERE api_key_id = :apiKeyId
                   AND usage_date = :usageDate
                   AND usage_count < :dailyLimit',
                $table
            ),
            [
                'apiKeyId' => $apiKey->getId(),
                'usageDate' => $dateValue,
                'dailyLimit' => $limit,
            ]
        );

        return $updated === 1;
    }
}
