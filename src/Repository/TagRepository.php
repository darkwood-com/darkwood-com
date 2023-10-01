<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TagRepository.
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findCachedById($id, $ttl)
    {
        $qb = $this->createQueryBuilder('t')->select('t, ts')->leftJoin('t.translations', 'ts')->where('t.id = :id')->orderBy('t.id', 'asc')->leftjoin('t.articles', 'p')->addSelect('p')->setParameter('id', $id);
        //$qb->getQuery()->useResultCache(true, $ttl, 'TagRepository::findCachedById' . ($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one for edit.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        $qb = $this->createQueryBuilder('t')->select('t')->leftJoin('t.translations', 'ts')->where('t.id = :id')->orderBy('t.id', 'asc')->leftjoin('t.articles', 'p')->addSelect('p')->setParameter('id', $id);
        //$qb->getQuery()->useResultCache(true, 120, 'TagRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Get one article.
     *
     * @param string      $title
     * @param string|null $locale
     *
     * @return mixed
     */
    public function findOneByTitle($title, $locale = null)
    {
        $qb = $this->createQueryBuilder('t')->select('t')->leftJoin('t.translations', 'ts')->andWhere('ts.title = :title')->setParameter('title', $title);
        if ($locale) {
            $qb->andWhere('ts.locale = :locale')->setParameter('locale', $locale);
        }

        //$qb->getQuery()->useResultCache(true, 120, 'TagRepository::findOneByTitle-'.$title);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get all tags.
     *
     * @param string|null $locale
     *
     * @return mixed
     */
    public function findAllAsArray($locale = null)
    {
        $qb = $this->createQueryBuilder('t')->leftJoin('t.translations', 'ts')->select('ts.title');
        if ($locale) {
            $qb->andWhere('ts.locale = :locale')->setParameter('locale', $locale);
        }

        //$qb->getQuery()->useResultCache(true, 120, 'TagRepository::findAllAsArray');
        return $qb->getQuery()->getResult();
    }
}
