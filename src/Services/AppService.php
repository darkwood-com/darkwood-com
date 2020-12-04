<?php

namespace App\Services;

use App\Entity\App;
use App\Entity\AppTranslation;
use App\Repository\AppRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
/**
 * Class AppService
 *
 * Object manager of appTranslation.
 */
class AppService
{
    /**
     * @var AppRepository
     */
    protected $appRepository;
    public function __construct(
        protected EntityManagerInterface $em
    )
    {
        $this->appRepository = $em->getRepository(App::class);
    }
    /**
     * Update a appTranslation.
     *
     * @return App
     */
    public function save(App $app, $invalidate = false)
    {
        $app->setUpdated(new \DateTime('now'));
        $this->em->persist($app);
        $this->em->flush();
        return $app;
    }
    /**
     * Remove one appTranslation.
     *
     * @param App $app
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
     *
     * @return object|null
     */
    public function findOneBy($filters = [])
    {
        return $this->appRepository->findOneBy($filters);
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
        return $this->appRepository->queryForSearch($filters, $order);
    }
    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return App|null
     */
    public function findOneToEdit($id, $locale)
    {
        return $this->appRepository->findOneToEdit($id, $locale);
    }
    /**
     * @param integer $id
     *
     * @return App|null
     */
    public function find($id)
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
