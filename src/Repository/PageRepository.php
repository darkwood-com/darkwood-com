<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PageRepository.
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function queryForSearch($filters = [], $type = null, $host = null, $locale = 'en', $order = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p', 'pts')->leftJoin('p.translations', 'pts')->leftJoin('p.site', 's')->andWhere('pts.locale = :locale')->setParameter('locale', $locale);
        if ($host) {
            $qb->andWhere('s.host = :host')->setParameter('host', $host);
        }
        if ($type == 'app') {
            $qb->andWhere('p INSTANCE OF App\Entity\App');
        } elseif ($type == 'page') {
            $qb->andWhere('p INSTANCE OF App\Entity\Page');
        }
        if ($order == 'normal') {
            $qb->addOrderBy('p.created', 'desc');
        }
        if (count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                if ($key == 'host') {
                    $qb->andWhere('s.host', $filter);
                }
                if ($key == 'limit_low') {
                    $qb->andWhere('p.created >= :low');
                    $qb->setParameter('low', $filter);
                    continue;
                }
                if ($key == 'limit_high') {
                    $qb->andWhere('p.created <= :high');
                    $qb->setParameter('high', $filter);
                    continue;
                }
                if ($key == 'allowEdit') {
                    $qb->andWhere('p.allowEdit = :allowEdit');
                    $qb->setParameter('allowEdit', $filter);
                    continue;
                }
                $qb->andWhere('p.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }
        //$qb->getQuery()->useResultCache(true, 120, 'PageRepository::queryForSearch');
        $query = $qb->getQuery();

        return $query;
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
        $qb = $this->createQueryBuilder('p')->select('p', 'pts')->leftJoin('p.translations', 'pts')->where('p.id = :id')->orderBy('p.id', 'asc')->setParameter('id', $id);
        //$qb->getQuery()->useResultCache(true, 120, 'PageRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Find one for edit.
     *
     * @return mixed
     */
    public function findAllBySite(Site $site = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p', 'pts')->leftJoin('p.translations', 'pts');
        if ($site !== null) {
            $qb->leftJoin('p.site', 's');
            $qb->andWhere('s.id = :id');
            $qb->setParameter('id', $site->getId());
        }
        //$qb->getQuery()->useResultCache(true, 120, 'PageRepository::findAll');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Find content by site.
     *
     * @param int $siteId
     *
     * @return array
     */
    public function findContentBySite($siteId)
    {
        $qb    = $this->createQueryBuilder('p')->select('p', 'pts', 's', 'br')->leftJoin('p.translations', 'pts')->leftjoin('p.site', 's')->andWhere('s.id = :siteId')->andWhere('p.active = TRUE')->setParameter('siteId', $siteId);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Find no content by site.
     *
     * @param int $siteId
     *
     * @return array
     */
    public function findNoContentBySite($siteId)
    {
        $qb    = $this->createQueryBuilder('p')->select('p', 'pts', 's', 'br')->leftJoin('p.translations', 'pts')->leftjoin('p.site', 's')->andWhere('s.id = :siteId')->andWhere('p.active = FALSE')->setParameter('siteId', $siteId);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param string $ref
     * @param string $host
     * @param null   $locale
     * @param null   $ttl
     *
     * @return Page|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneActiveByRefAndHost($ref, $host, $locale = null, $ttl = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p', 'pts', 's')->leftJoin('p.translations', 'pts')->leftJoin('p.site', 's')->andWhere('pts.active = TRUE')->andWhere('p.ref = :ref')->setParameter('ref', $ref)->andWhere('s.host = :host')->setParameter('host', $host)->andWhere('s.active = TRUE');
        if (!is_null($locale)) {
            $qb->andWhere('pts.locale = :locale')->setParameter('locale', $locale);
        }
        $query = $qb->getQuery();
        //$query->useResultCache(!is_null($ttl), $ttl, 'PageRepository::findOneActiveByRefAndHost'.$siteId.($navigationSlug ? $navigationSlug : '').$slug.$is301?'0':'1');
        return $query->getOneOrNullResult();
    }

    /**
     * Find one public.
     *
     * @param string   $ref
     * @param string   $locale
     * @param int|null $ttl
     */
    public function findOneByRef($ref, $locale = null, $ttl = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p', 'pts')->leftJoin('p.translations', 'pts')->andWhere('pts.active = TRUE')->andWhere('p.ref = :ref')->setParameter('ref', $ref);
        if (!is_null($locale)) {
            $qb->andWhere('pts.locale = :locale')->setParameter('locale', $locale);
        }
        $query = $qb->getQuery();
        //$query->useResultCache(!is_null($ttl), $ttl, 'PageRepository::findOnePublic'.$siteId.($navigationSlug ? $navigationSlug : '').$slug.$is301?'0':'1');
        return $query->getOneOrNullResult();
    }

    public function findActives($locale = null, $type = null, $host = null)
    {
        $qb = $this->createQueryBuilder('p')->select('p', 'pts')->leftJoin('p.translations', 'pts')->addOrderBy('p.created', 'desc')->andWhere('pts.active = TRUE');
        if (!is_null($locale)) {
            $qb->andWhere('pts.locale = :locale')->setParameter('locale', $locale);
        }
        if (!is_null($host)) {
            $qb->leftJoin('p.site', 's');
            $qb->andWhere('s.host = :host')->setParameter('host', $host);
        }
        if ($type == 'app') {
            $qb->andWhere('p INSTANCE OF App\Entity\App');
        } elseif ($type == 'page') {
            $qb->andWhere('p INSTANCE OF App\Entity\Page');
        }
        $query = $qb->getQuery();
        //$query->useResultCache(!is_null($ttl), $ttl, 'PageRepository::findOnePublic'.$siteId.($navigationSlug ? $navigationSlug : '').$slug.$is301?'0':'1');
        return $query->getResult();
    }
}
