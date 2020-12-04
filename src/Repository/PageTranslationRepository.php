<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\PageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class PageTranslationRepository.
 */
class PageTranslationRepository extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\PageTranslation::class);
    }
    public function findOneByPageAndLocale(\App\Entity\Page $page, $locale)
    {
        return $this->createQueryBuilder('pt')->andWhere('pt.page = :page')->setParameter('page', $page)->andWhere('pt.locale = :locale')->setParameter('locale', $locale)->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }
}
