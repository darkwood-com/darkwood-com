<?php

namespace App\Repository;

use App\Entity\AppContent;
use App\Entity\ArticleTranslation;
use App\Repository\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ArticleTranslationRepository.
 */
class ArticleTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleTranslation::class);
    }
}
