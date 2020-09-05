<?php

namespace App\Services;

use App\Entity\App;
use App\Entity\AppContent;
use App\Entity\AppTranslation;
use App\Entity\Page;
use App\Repository\AppContentRepository;
use App\Repository\PageRepository;
use App\Services\BaseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class AppContentService
 *
 * Object manager of appTranslation.
 */
class AppContentService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var AppContentRepository
     */
    protected $appContentRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->appContentRepository = $em->getRepository(AppContent::class);
    }

    /**
     * Update a appTranslation.
     *
     * @param AppContent $appContent
     *
     * @return AppContent
     */
    public function save(AppContent $appContent, $invalidate = false)
    {
        $appContent->setUpdated(new \DateTime('now'));

        $this->em->persist($appContent);
        $this->em->flush();

        return $appContent;
    }

    /**
     * Remove one appTranslation.
     *
     * @param AppContent $appContent
     */
    public function remove(AppContent $appContent)
    {
        $this->em->remove($appContent);
        $this->em->flush();
    }

    /**
     * Find one by filters.
     *
     * @param array $filters
     *
     * @return null|object
     */
    public function findOneBy($filters = array())
    {
        return $this->appContentRepository->findOneBy($filters);
    }

    /**
     * Search.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function getQueryForSearch($filters = array(), $order = 'normal')
    {
        return $this->appContentRepository->queryForSearch($filters, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return AppContent|null
     */
    public function findOneToEdit($id)
    {
        return $this->appContentRepository->findOneToEdit($id);
    }

    /**
     * @param $id
     *
     * @return null|AppContent
     */
    public function find($id)
    {
        return $this->appContentRepository->find($id);
    }

    /**
     * Find all.
     */
    public function findAll()
    {
        return $this->appContentRepository->findAll();
    }

    public function findActives($limit = null)
    {
        return $this->appContentRepository->findActives($limit);
    }
}
