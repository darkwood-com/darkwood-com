<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Enum\ArticleType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Override;

/**
 * Class ArticleRepository.
 *
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     */
    public function queryForSearch($filters = [], $locale = 'en', $order = null): Query
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        if ($order === 'normal') {
            $qb->addOrderBy('n.created', 'desc');
        }

        if ($filters !== []) {
            foreach ($filters as $key => $filter) {
                if ($key === 'limit_low') {
                    $qb->andWhere('n.created >= :low');
                    $qb->setParameter('low', $filter);

                    continue;
                }

                if ($key === 'limit_high') {
                    $qb->andWhere('n.created <= :high');
                    $qb->setParameter('high', $filter);

                    continue;
                }

                $qb->andWhere('n.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }

        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::queryForSearch');
        return $qb->getQuery();
    }

    /**
     * Find one for edit.
     *
     * @param int $id
     */
    public function findOneToEdit($id): mixed
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->where('n.id = :id')->orderBy('n.id', 'asc')->setParameter('id', $id);
        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findOneBySlug($slug, $locale)
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->andWhere('nts.slug = :slug')->setParameter('slug', $slug)->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one for edit.
     *
     * @param array $parameters
     */
    #[Override]
    public function findAll($parameters = []): array
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->addOrderBy('n.created', 'desc');
        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findAll');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findActivesQueryBuilder($locale = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('n', 'nts')
            ->leftJoin('n.translations', 'nts')
            ->addOrderBy('n.created', 'desc')
            ->andWhere('nts.active = true')
            ->andWhere('n.type = :manualType')
            ->setParameter('manualType', ArticleType::Manual)
        ;
        if ($locale) {
            $qb->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function findWatchActivesQueryBuilder($locale = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('n', 'nts')
            ->leftJoin('n.translations', 'nts')
            ->andWhere('n.type = :type')
            ->setParameter('type', ArticleType::Watch)
            ->addOrderBy('n.created', 'desc')
        ;

        if ($locale) {
            $qb->andWhere('(nts.locale = :locale OR nts.id IS NULL)')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function findOneByGenerationId(string $generationId): ?Article
    {
        return $this->findOneBy(['generationId' => $generationId]);
    }

    public function findOneBySlugAndLocale(string $slug, string $locale): ?Article
    {
        $qb = $this->createQueryBuilder('n')
            ->select('n', 'nts')
            ->leftJoin('n.translations', 'nts')
            ->andWhere('nts.slug = :slug')
            ->andWhere('nts.locale = :locale')
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findActives($locale = null, $limit = null): Paginator
    {
        $qb = $this->createQueryBuilder('n')->select('n', 'nts')->leftJoin('n.translations', 'nts')->addOrderBy('n.created', 'desc')->andWhere('nts.active = true');
        if ($locale) {
            $qb->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        // $qb->getQuery()->useResultCache(true, 120, 'ArticleRepository::findActives');
        $query = $qb->getQuery();

        return new Paginator($query);
    }

    /**
     * @return Paginator<Article>
     */
    public function findManualActives(?string $locale = null, ?int $limit = null): Paginator
    {
        $qb = $this->findActivesQueryBuilder($locale, $limit);

        return new Paginator($qb->getQuery());
    }

    public function findLatestWatchArticle(?string $locale): ?Article
    {
        $qb = $this->findWatchActivesQueryBuilder($locale, 1);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Article ? $result : null;
    }

    public function findReleaseActivesQueryBuilder($locale = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('n', 'nts')
            ->leftJoin('n.translations', 'nts')
            ->andWhere('n.type = :type')
            ->setParameter('type', ArticleType::Release)
            ->addOrderBy('n.created', 'desc')
        ;

        if ($locale) {
            $qb->andWhere('(nts.locale = :locale OR nts.id IS NULL)')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function findLatestReleaseArticle(?string $locale): ?Article
    {
        $qb = $this->findReleaseActivesQueryBuilder($locale, 1);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Article ? $result : null;
    }

    public function findCreatorActivesQueryBuilder($locale = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->select('n', 'nts')
            ->leftJoin('n.translations', 'nts')
            ->andWhere('n.type = :type')
            ->setParameter('type', ArticleType::Creator)
            ->addOrderBy('n.created', 'desc')
        ;

        if ($locale) {
            $qb->andWhere('(nts.locale = :locale OR nts.id IS NULL)')->setParameter('locale', $locale);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function findLatestCreatorArticle(?string $locale): ?Article
    {
        $qb = $this->findCreatorActivesQueryBuilder($locale, 1);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Article ? $result : null;
    }

    /**
     * @return list<Article>
     */
    public function findRelatedBySharedTags(Article $article, ?string $locale = null, int $limit = 4): array
    {
        $tagIds = [];
        foreach ($article->getTags() as $tag) {
            $tagIds[] = $tag->getId();
        }

        if ([] === $tagIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('n')
            ->select('n', 'nts')
            ->leftJoin('n.translations', 'nts')
            ->innerJoin('n.tags', 't')
            ->andWhere('t.id IN (:tagIds)')
            ->andWhere('n.id != :articleId')
            ->andWhere('nts.active = true')
            ->setParameter('tagIds', $tagIds)
            ->setParameter('articleId', $article->getId())
            ->addOrderBy('n.created', 'desc')
            ->setMaxResults(max(1, $limit))
        ;

        if ($locale) {
            $qb->andWhere('nts.locale = :locale')->setParameter('locale', $locale);
        }

        /** @var list<Article> $articles */
        $articles = $qb->getQuery()->getResult();

        return $articles;
    }
}
