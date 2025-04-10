<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Repository\ArticleRepository;
use App\Repository\ArticleTranslationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Storage\StorageInterface;

use function count;

/**
 * Class ArticleService.
 *
 * Object manager of articleTranslation.
 */
class ArticleService
{
    /**
     * @var ArticleRepository
     */
    protected ArticleRepository $articleRepository;

    /**
     * @var ArticleTranslationRepository
     */
    protected ArticleTranslationRepository $articleTranslationRepository;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameterBagInterface,
        protected StorageInterface $storage
    ) {
        /** @var ArticleRepository $repository */
        $repository = $em->getRepository(Article::class);
        $this->articleRepository = $repository;

        /** @var ArticleTranslationRepository $translationRepository */
        $translationRepository = $em->getRepository(ArticleTranslation::class);
        $this->articleTranslationRepository = $translationRepository;
    }

    /**
     * Update a articleTranslation.
     */
    public function save(Article $article, $invalidate = false): Article
    {
        $article->setUpdated(new DateTime('now'));
        foreach ($article->getTranslations() as $translation) {
            $translation->setUpdated(new DateTime('now'));
        }

        $this->em->persist($article);
        $this->em->flush();

        return $article;
    }

    /**
     * Remove one articleTranslation.
     */
    public function remove(Article $article)
    {
        $this->em->remove($article);
        $this->em->flush();
    }

    public function duplicate(ArticleTranslation $articleTranslation, $locale)
    {
        $article = $articleTranslation->getArticle();
        $duplicateArticleTranslation = $this->articleTranslationRepository->findOneByArticleAndLocale($article, $locale);
        if (!$duplicateArticleTranslation) {
            $duplicateArticleTranslation = new ArticleTranslation();
            $duplicateArticleTranslation->setArticle($article);
            $duplicateArticleTranslation->setLocale($locale);
        }

        $duplicateArticleTranslation->setCreated($articleTranslation->getCreated());
        $duplicateArticleTranslation->setTitle($articleTranslation->getTitle());
        $duplicateArticleTranslation->setSlug($articleTranslation->getSlug());
        $duplicateArticleTranslation->setContent($articleTranslation->getContent());
        $duplicateArticleTranslation->setActive($articleTranslation->getActive());
        if ($articleTranslation->getImageName() !== null) {
            $imageUrl = $this->storage->resolvePath($articleTranslation, 'image');
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent) {
                $imageName = basename(md5((string) time()) . preg_replace('/\?.*$/', '', $imageUrl));
                $tmpFile = sys_get_temp_dir() . '/pt-' . $imageName;
                file_put_contents($tmpFile, $imageContent);
                $image = new UploadedFile($tmpFile, $imageName, null, null, true);
                $duplicateArticleTranslation->setImage($image);
            }
        }

        return $duplicateArticleTranslation;
    }

    /**
     * Update a articleTranslation.
     */
    public function saveTranslation(ArticleTranslation $articleTranslation, $exportLocales = false): ArticleTranslation
    {
        $articleTranslation->setUpdated(new DateTime('now'));
        $this->em->persist($articleTranslation);
        $this->em->flush();
        if ($exportLocales) {
            foreach ($this->parameterBagInterface->get('app_locales') as $locale) {
                if ($locale !== $articleTranslation->getLocale()) {
                    $exportPageTranslation = $this->duplicate($articleTranslation, $locale);
                    $this->em->persist($exportPageTranslation);
                    $this->em->flush();
                }
            }
        }

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
     */
    public function findOneBy($filters = []): ?object
    {
        return $this->articleRepository->findOneBy($filters);
    }

    /**
     * @param string $slug
     * @param string $locale
     */
    public function findOneBySlug($slug, $locale): ?Article
    {
        return $this->articleRepository->findOneBySlug($slug, $locale);
    }

    /**
     * Search.
     *
     * @param array $filters
     */
    public function getQueryForSearch($filters = [], $locale = 'en', $order = 'normal'): Query
    {
        return $this->articleRepository->queryForSearch($filters, $locale, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id): ?Article
    {
        return $this->articleRepository->findOneToEdit($id);
    }

    /**
     * @param int $id
     */
    public function find($id): ?ArticleTranslation
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

    public function findActivesQueryBuilder($locale = null, $limit = null)
    {
        return $this->articleRepository->findActivesQueryBuilder($locale, $limit);
    }

    /**
     * @param null $locale
     * @param null $limit
     *
     * @return Paginator<Article>
     */
    public function findActives($locale = null, $limit = null): Paginator
    {
        return $this->articleRepository->findActives($locale, $limit);
    }
}
