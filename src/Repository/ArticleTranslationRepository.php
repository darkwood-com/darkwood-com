<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * Class ArticleTranslationRepository.
 */
class ArticleTranslationRepository extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\ArticleTranslation::class);
    }
    public function findByArticle(\App\Entity\Article $article)
    {
        return $this->createQueryBuilder('pt')->andWhere('pt.article = :article')->setParameter('article', $article)->getQuery()->getResult();
    }
    public function findOneByArticleAndLocale(\App\Entity\Article $article, $locale)
    {
        return $this->createQueryBuilder('pt')->andWhere('pt.article = :article')->setParameter('article', $article)->andWhere('pt.locale = :locale')->setParameter('locale', $locale)->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }
}
