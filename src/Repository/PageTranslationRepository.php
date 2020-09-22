<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\PageTranslation;
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

    public function findOneByPageAndLocale(Page $page, $locale)
    {
        return $this->createQueryBuilder('pt')
            ->andWhere('pt.page = :page')->setParameter('page', $page)
            ->andWhere('pt.locale = :locale')->setParameter('locale', $locale)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
