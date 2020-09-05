<?php

namespace App\Services;

use App\Entity\App;
use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Repository\AppRepository;
use App\Repository\ArticleRepository;
use App\Services\BaseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Class ArticleService
 *
 * Object manager of articleTranslation.
 */
class ArticleService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->articleRepository = $em->getRepository(Article::class);
    }

    /**
     * Update a articleTranslation.
     *
     * @param Article $article
     *
     * @return Article
     */
    public function save(Article $article, $invalidate = false)
    {
        $article->setUpdated(new \DateTime('now'));
        foreach ($article->getTranslations() as $translation) {
            $translation->setUpdated(new \DateTime('now'));
        }
        $this->em->persist($article);
        $this->em->flush();

        return $article;
    }

    /**
     * Remove one articleTranslation.
     *
     * @param ArticleTranslation $articleTranslation
     */
    public function remove(Article $article)
    {
        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * Update a articleTranslation.
     *
     * @param ArticleTranslation $articleTranslation
     *
     * @return ArticleTranslation
     */
    public function saveTranslation(ArticleTranslation $articleTranslation, $invalidate = false)
    {
        $articleTranslation->setUpdated(new \DateTime('now'));
        $this->em->persist($articleTranslation);
        $this->em->flush();

        return $articleTranslation;
    }

    public function removeTranslation(ArticleTranslation $articleTs)
    {
        $nbT = count($articleTs->getArticle()->getTranslations());
        if ($nbT <= 1) {
            $this->remove($articleTs->getArticle());

            return;
        }

        $this->em->remove($articleTs);
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
        return $this->articleRepository->findOneBy($filters);
    }

    /**
     * @param $slug
     * @param $locale
     *
     * @return Article
     */
    public function findOneBySlug($slug, $locale)
    {
        return $this->articleRepository->findOneBySlug($slug, $locale);
    }

    /**
     * Search.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function getQueryForSearch($filters = array(), $locale, $order = 'normal')
    {
        return $this->articleRepository->queryForSearch($filters, $locale, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return Article|null
     */
    public function findOneToEdit($id)
    {
        return $this->articleRepository->findOneToEdit($id);
    }

    /**
     * @param $id
     *
     * @return null|ArticleTranslation
     */
    public function find($id)
    {
        return $this->articleRepository->find($id);
    }

    /**
     * Find all.
     */
    public function findAll()
    {
        return $this->articleRepository->findAll();
    }

    /**
     * @param null $locale
     * @param null $limit
     * @return Article[]
     */
    public function findActives($locale = null, $limit = null)
    {
        return $this->articleRepository->findActives($locale, $limit);
    }
}
