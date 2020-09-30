<?php

namespace App\Services;

use App\Entity\AppContent;
use App\Entity\AppTranslation;
use App\Repository\AppContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

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
        $this->em                   = $em;
        $this->appContentRepository = $em->getRepository(AppContent::class);
    }

    /**
     * Update a appTranslation.
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
     * @return object|null
     */
    public function findOneBy($filters = [])
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
    public function getQueryForSearch($filters = [], $order = 'normal')
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
     * @param integer $id
     *
     * @return AppContent|null
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
