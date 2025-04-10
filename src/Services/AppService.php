<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\App;
use App\Entity\AppTranslation;
use App\Repository\AppRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Class AppService.
 *
 * Object manager of appTranslation.
 */
class AppService
{
    protected AppRepository $appRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        /** @var AppRepository $repository */
        $repository = $em->getRepository(App::class);
        $this->appRepository = $repository;
    }

    /**
     * Update a appTranslation.
     */
    public function save(App $app, $invalidate = false): App
    {
        $app->setUpdated(new DateTime('now'));
        $this->em->persist($app);
        $this->em->flush();

        return $app;
    }

    /**
     * Remove one appTranslation.
     */
    public function remove(App $app)
    {
        $this->em->remove($app);
        $this->em->flush();
    }

    /**
     * Find one by filters.
     *
     * @param array $filters
     */
    public function findOneBy($filters = []): ?object
    {
        return $this->appRepository->findOneBy($filters);
    }

    /**
     * Search.
     *
     * @param array $filters
     */
    public function getQueryForSearch($filters = [], $order = 'normal'): Query
    {
        return $this->appRepository->queryForSearch($filters, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id, $locale): ?App
    {
        return $this->appRepository->findOneToEdit($id, $locale);
    }

    /**
     * @param int $id
     */
    public function find($id): ?App
    {
        return $this->appRepository->find($id);
    }

    /**
     * Find all.
     */
    public function findAll()
    {
        return $this->appRepository->findAll();
    }

    public function findActives($limit = null)
    {
        return $this->appRepository->findActives($limit);
    }
}
