<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\AppContent;
use App\Entity\AppTranslation;
use App\Repository\AppContentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Class AppContentService.
 *
 * Object manager of appTranslation.
 */
class AppContentService
{
    /**
     * @var AppContentRepository
     */
    protected AppContentRepository $appContentRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        /** @var AppContentRepository $repository */
        $repository = $em->getRepository(AppContent::class);
        $this->appContentRepository = $repository;
    }

    /**
     * Update a appTranslation.
     */
    public function save(AppContent $appContent, $invalidate = false): AppContent
    {
        $appContent->setUpdated(new DateTime('now'));
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
     */
    public function findOneBy($filters = []): ?object
    {
        return $this->appContentRepository->findOneBy($filters);
    }

    /**
     * Search.
     *
     * @param array $filters
     */
    public function getQueryForSearch($filters = [], $order = 'normal'): Query
    {
        return $this->appContentRepository->queryForSearch($filters, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id): ?AppContent
    {
        return $this->appContentRepository->findOneToEdit($id);
    }

    /**
     * @param int $id
     */
    public function find($id): ?AppContent
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
