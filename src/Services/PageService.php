<?php

declare(strict_types=1);

namespace App\Services;

use App\Controller\AppsController;
use App\Entity\App;
use App\Entity\AppContent;
use App\Entity\ArticleTranslation;
use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\Site;
use App\Repository\AppContentRepository;
use App\Repository\ArticleTranslationRepository;
use App\Repository\PageRepository;
use App\Repository\PageTranslationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

use function count;

/**
 * Class PageService.
 *
 * Object manager of entity.
 */
class PageService
{
    /**
     * @var PageRepository
     */
    protected EntityRepository $pageRepository;

    /**
     * @var PageTranslationRepository
     */
    protected EntityRepository $entityRepository;

    /**
     * @var ArticleTranslationRepository
     */
    protected EntityRepository $articleTranslationRepository;

    /**
     * @var AppContentRepository
     */
    protected EntityRepository $appContentRepository;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameterBagInterface,
        protected CacheInterface $appCache,
        protected RouterInterface $router,
        protected StorageInterface $storage
    ) {
        $this->pageRepository = $em->getRepository(Page::class);
        $this->entityRepository = $em->getRepository(PageTranslation::class);
        $this->articleTranslationRepository = $em->getRepository(ArticleTranslation::class);
        $this->appContentRepository = $em->getRepository(AppContent::class);
    }

    /**
     * Update a entity.
     */
    public function save(Page $page, $invalidate = false): Page
    {
        $page->setUpdated(new DateTime('now'));
        foreach ($page->getTranslations() as $translation) {
            $translation->setUpdated(new DateTime('now'));
        }

        $this->em->persist($page);
        $this->em->flush();

        return $page;
    }

    /**
     * Remove one entity.
     */
    public function remove(Page $page)
    {
        $this->em->remove($page);
        $this->em->flush();
    }

    public function duplicate(PageTranslation $entity, $locale)
    {
        $page = $entity->getPage();
        $duplicatePageTranslation = $this->entityRepository->findOneByPageAndLocale($page, $locale);
        if (!$duplicatePageTranslation) {
            $duplicatePageTranslation = new PageTranslation();
            $duplicatePageTranslation->setPage($page);
            $duplicatePageTranslation->setLocale($locale);
        }

        $duplicatePageTranslation->setTitle($entity->getTitle());
        $duplicatePageTranslation->setDescription($entity->getDescription());
        $duplicatePageTranslation->setContent($entity->getContent());
        $duplicatePageTranslation->setActive($entity->getActive());
        if ($entity->getImageName() !== '' && $entity->getImageName() !== '0') {
            $imageUrl = $this->storage->resolvePath($entity, 'image');
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent) {
                $imageName = basename((string) preg_replace('/\?.*$/', '', $imageUrl));
                $tmpFile = sys_get_temp_dir() . '/pt-' . $imageName;
                file_put_contents($tmpFile, $imageContent);
                $image = new UploadedFile($tmpFile, $imageName, null, null, true);
                $duplicatePageTranslation->setImage($image);
            }
        }

        if ($page instanceof App) {
            $oldContents = $this->appContentRepository->findByAppAndLocale($page, $locale);
            foreach ($oldContents as $oldContent) {
                $oldContent->setApp(null);
                $this->em->remove($oldContent);
            }

            $this->em->flush();
            $contents = $this->appContentRepository->findByAppAndLocale($page, $entity->getLocale());
            foreach ($contents as $content) {
                $duplicateContent = new AppContent();
                $duplicateContent->setApp($page);
                $duplicateContent->setLocale($locale);
                $duplicateContent->setTitle($content->getTitle());
                $duplicateContent->setContent($content->getContent());
                $duplicateContent->setPosition($content->getPosition());
                $this->em->persist($duplicateContent);
            }

            $this->em->flush();
        }

        return $duplicatePageTranslation;
    }

    /**
     * Update a entity.
     */
    public function saveTranslation(PageTranslation $entity, $exportLocales = false): PageTranslation
    {
        $entity->setUpdated(new DateTime('now'));
        $this->em->persist($entity);
        $this->em->flush();
        if ($exportLocales) {
            foreach ($this->parameterBagInterface->get('app_locales') as $locale) {
                if ($locale !== $entity->getLocale()) {
                    $exportPageTranslation = $this->duplicate($entity, $locale);
                    $this->em->persist($exportPageTranslation);
                    $this->em->flush();
                }
            }
        }

        return $entity;
    }

    public function removeTranslation(PageTranslation $pageTs)
    {
        $nbT = count($pageTs->getPage()->getTranslations());
        if ($nbT <= 1) {
            $this->remove($pageTs->getPage());

            return;
        }

        $this->em->remove($pageTs);
        $this->em->flush();
    }

    /**
     * Find one by filters.
     *
     * @param array $filters
     */
    public function findOneBy($filters = []): ?object
    {
        return $this->pageRepository->findOneBy($filters);
    }

    /**
     * Search.
     *
     * @param array $filters
     */
    public function getQueryForSearch($filters = [], $type = null, $host = null, $locale = 'en', $order = 'normal'): Query
    {
        return $this->pageRepository->queryForSearch($filters, $type, $host, $locale, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id): ?Page
    {
        return $this->pageRepository->findOneToEdit($id);
    }

    /**
     * @param string $ref
     * @param string $host
     * @param null   $locale
     */
    public function findOneActiveByRefAndHost($ref, $host, $locale = null): ?Page
    {
        return $this->pageRepository->findOneActiveByRefAndHost($ref, $host, $locale);
    }

    /**
     * @param string $ref
     * @param null   $locale
     */
    public function findOneByRef($ref, $locale = null): ?Page
    {
        return $this->pageRepository->findOneByRef($ref, $locale);
    }

    /**
     * @param null $locale
     * @param null $type
     *
     * @return Page[]
     */
    public function findActives($locale = null, $type = null, $host = null): array
    {
        return $this->pageRepository->findActives($locale, $type, $host);
    }

    /**
     * @param int $id
     */
    public function find($id): ?PageTranslation
    {
        return $this->pageRepository->find($id);
    }

    /**
     * Find all.
     */
    public function findAllBySite(?Site $site = null)
    {
        return $this->pageRepository->findAllBySite($site);
    }

    public function getUrl($entity, $referenceType = UrlGeneratorInterface::NETWORK_PATH, $force = false)
    {
        $cacheId = 'page_url-' . md5($entity->getId() . '-' . $entity::class . '-' . $referenceType);

        return $this->appCache->get($cacheId, function (ItemInterface $item) use ($entity, $referenceType) {
            $item->expiresAfter(43200); // 12 hours
            if ($entity instanceof PageTranslation) {
                $site = $entity->getPage()->getSite();
                $routes = $this->router->getRouteCollection();
                $routeData = null;
                foreach ($routes as $route) {
                    $page = $entity->getPage();
                    /** @var Route $route */
                    if ($page instanceof App && $route->getDefault('_controller') === AppsController::class . '::app') {
                        $routeLocale = $route->getDefault('_locale');
                        $host = $site->getHost();
                        if ($route->getHost() && $route->getHost() !== $host) {
                            continue;
                        }

                        if ($routeLocale === $entity->getLocale()) {
                            $routeData = ['route' => $route, 'name' => $route->getDefault('_canonical_route'), 'params' => array_merge($route->getDefaults(), ['_locale' => $routeLocale, 'ref' => $page->getRef()])];

                            break;
                        }
                    } elseif (str_starts_with((string) $route->getDefault('_controller'), 'App\Controller') && $route->getDefault('ref') === $entity->getPage()->getRef()) {
                        $routeLocale = $route->getDefault('_locale');
                        $host = $site->getHost();
                        if ($route->getHost() && $route->getHost() !== $host) {
                            continue;
                        }

                        if ($routeLocale === $entity->getLocale()) {
                            $routeData = ['route' => $route, 'name' => $route->getDefault('_canonical_route'), 'params' => array_merge($route->getDefaults(), ['_locale' => $routeLocale])];

                            break;
                        }
                    }
                }

                if ($routeData) {
                    return $this->router->generate($routeData['name'], $routeData['params'], $referenceType);
                }
            } elseif ($entity instanceof ArticleTranslation) {
                return $this->router->generate('blog_article', ['_locale' => $entity->getLocale(), 'slug' => $entity->getSlug()], $referenceType);
            }

            return null;
        }, $force ? INF : null);
    }

    public function getPageUrl($ref, $host, $locale = null)
    {
        $page = $this->pageRepository->findOneActiveByRefAndHost($ref, $host, $locale);
        if ($page === null) {
            return null;
        }

        return $this->getUrl($page->getOneTranslation($locale));
    }

    public function getPageCanonical($ref, $entity, $host)
    {
        $pageLinks = $this->getPageLinks($ref, $entity, $host, null, UrlGeneratorInterface::ABSOLUTE_URL);
        foreach ($pageLinks as $locale => $url) {
            return ['locale' => $locale, 'url' => $url];
        }

        return null;
    }

    public function getPageLinks($ref, $entity, $host, $locale = null, $referenceType = UrlGeneratorInterface::NETWORK_PATH)
    {
        $pageLinks = [];
        if ($entity instanceof ArticleTranslation) {
            $articleTranslations = $this->articleTranslationRepository->findByArticle($entity->getArticle());
            foreach ($articleTranslations as $articleTranslation) {
                if ($articleTranslation->getLocale() === $locale) {
                    continue;
                }

                $pageLinks[$articleTranslation->getLocale()] = $this->getUrl($articleTranslation, $referenceType);
            }
        } else {
            $page = $this->pageRepository->findOneActiveByRefAndHost($ref, $host);
            if ($page !== null) {
                foreach ($page->getTranslations() as $pageTranslation) {
                    if ($pageTranslation->getLocale() === $locale) {
                        continue;
                    }

                    $pageLinks[$pageTranslation->getLocale()] = $this->getUrl($pageTranslation, $referenceType);
                }
            }
        }

        return $pageLinks;
    }
}
