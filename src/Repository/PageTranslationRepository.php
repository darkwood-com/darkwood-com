<?php

namespace App\Repository;

use App\Entity\AppContent;
use App\Entity\PageTranslation;
use App\Repository\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PageTranslationRepository.
 */
class PageTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageTranslation::class);
    }
}
