<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DarkwoodArchive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|DarkwoodArchive find($id, $lockMode = null, $lockVersion = null)
 * @method null|DarkwoodArchive findOneBy(array $criteria, array $orderBy = null)
 * @method DarkwoodArchive[]    findAll()
 * @method DarkwoodArchive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DarkwoodArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DarkwoodArchive::class);
    }

    public function findOneByDateId(string $dateId): ?DarkwoodArchive
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateId);
        if ($date === false) {
            return null;
        }

        return $this->findOneBy(['archiveDate' => $date], null);
    }

    /** @return DarkwoodArchive[] */
    public function findAllOrderByDateDesc(): array
    {
        return $this->findBy([], ['archiveDate' => 'DESC']);
    }
}
