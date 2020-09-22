<?php

namespace App\Services;

use App\Entity\App;
use App\Entity\AppContent;
use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\Site;
use App\Repository\AppContentRepository;
use App\Repository\PageRepository;
use App\Repository\PageTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class PageService
 *
 * Object manager of pageTranslation.
 */
class PageService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ParameterBagInterface
     */
    protected $parameterBagInterface;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var PageTranslationRepository
     */
    protected $pageTranslationRepository;

    /**
     * @var AppContentRepository
     */
    protected $appContentRepository;

    /**
     * @var CacheInterface
     */
    protected $appCache;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var StorageInterface
     */
    protected $storage;

    public function __construct(
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBagInterface,
        CacheInterface $appCache,
        RouterInterface $router,
        StorageInterface $storage
    ) {
        $this->em                        = $em;
        $this->pageRepository            = $em->getRepository(Page::class);
        $this->pageTranslationRepository = $em->getRepository(PageTranslation::class);
        $this->appContentRepository      = $em->getRepository(AppContent::class);
        $this->parameterBagInterface     = $parameterBagInterface;
        $this->appCache                  = $appCache;
        $this->router                    = $router;
        $this->storage            = $storage;
    }

    /**
     * Update a pageTranslation.
     *
     * @return Page
     */
    public function save(Page $page, $invalidate = false)
    {
        $page->setUpdated(new \DateTime('now'));
        foreach ($page->getTranslations() as $translation) {
            $translation->setUpdated(new \DateTime('now'));
        }
        $this->em->persist($page);
        $this->em->flush();

        return $page;
    }

    /**
     * Remove one pageTranslation.
     *
     * @param PageTranslation $pageTranslation
     */
    public function remove(Page $page)
    {
        $this->em->remove($page);
        $this->em->flush();
    }

    public function duplicate(PageTranslation $pageTranslation, $locale)
    {
        $page = $pageTranslation->getPage();

        $duplicatePageTranslation = $this->pageTranslationRepository->findOneByPageAndLocale($page, $locale);
        if(!$duplicatePageTranslation) {
            $duplicatePageTranslation = new PageTranslation();
            $duplicatePageTranslation->setPage($page);
            $duplicatePageTranslation->setLocale($locale);
        }

        $duplicatePageTranslation->setTitle($pageTranslation->getTitle());
        $duplicatePageTranslation->setDescription($pageTranslation->getDescription());
        $duplicatePageTranslation->setContent($pageTranslation->getContent());
        $duplicatePageTranslation->setActive($pageTranslation->getActive());
        
        if($pageTranslation->getImageName()) {
            $imageUrl = $this->storage->resolvePath($pageTranslation, 'image');
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent) {
                $imageName = basename(preg_replace('/\?.*$/', '', $imageUrl));
                $tmpFile   = sys_get_temp_dir() . '/pt-' . $imageName;
                file_put_contents($tmpFile, $imageContent);

                $image = new UploadedFile($tmpFile, $imageName, null, null, true);
                $duplicatePageTranslation->setImage($image);
            }
        }

        if($page instanceof App) {
            $oldContents = $this->appContentRepository->findByAppAndLocale($page, $locale);
            foreach($oldContents as $oldContent) {
                $oldContent->setApp(null);
                $this->em->remove($oldContent);
            }
            $this->em->flush();

            $contents = $this->appContentRepository->findByAppAndLocale($page, $pageTranslation->getLocale());
            foreach($contents as $content) {
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
     * Update a pageTranslation.
     *
     * @return PageTranslation
     */
    public function saveTranslation(PageTranslation $pageTranslation, $exportLocales = false)
    {
        $pageTranslation->setUpdated(new \DateTime('now'));
        $this->em->persist($pageTranslation);
        $this->em->flush();

        if($exportLocales) {
            foreach($this->parameterBagInterface->get('app_locales') as $locale) {
                if($locale !== $pageTranslation->getLocale()) {
                    $exportPageTranslation = $this->duplicate($pageTranslation, $locale);
                    $this->em->persist($exportPageTranslation);
                }
            }
            $this->em->flush();
        }

        return $pageTranslation;
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
     *
     * @return object|null
     */
    public function findOneBy($filters = [])
    {
        return $this->pageRepository->findOneBy($filters);
    }

    /**
     * Search.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function getQueryForSearch($filters = [], $type, $host, $locale, $order = 'normal')
    {
        return $this->pageRepository->queryForSearch($filters, $type, $host, $locale, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return Page|null
     */
    public function findOneToEdit($id)
    {
        return $this->pageRepository->findOneToEdit($id);
    }

    /**
     * @param $ref
     * @param $host
     * @param null $locale
     *
     * @return Page|null
     */
    public function findOneActiveByRefAndHost($ref, $host, $locale = null)
    {
        return $this->pageRepository->findOneActiveByRefAndHost($ref, $host, $locale);
    }

    /**
     * @param $ref
     * @param null $locale
     *
     * @return Page|null
     */
    public function findOneByRef($ref, $locale = null)
    {
        return $this->pageRepository->findOneByRef($ref, $locale);
    }

    /**
     * @param null $locale
     * @param null $type
     *
     * @return Page[]
     */
    public function findActives($locale = null, $type = null, $host = null)
    {
        return $this->pageRepository->findActives($locale, $type, $host);
    }

    /**
     * @param $id
     *
     * @return PageTranslation|null
     */
    public function find($id)
    {
        return $this->pageRepository->find($id);
    }

    /**
     * Find all.
     */
    public function findAllBySite(Site $site = null)
    {
        return $this->pageRepository->findAllBySite($site);
    }

    /**
     * @param bool $absolute
     *
     * @return mixed
     */
    public function getUrl(PageTranslation $pageTranslation, $referenceType = UrlGeneratorInterface::NETWORK_PATH, $force = false)
    {
        $cacheId = 'page_url-' . $pageTranslation->getId() . '-' . $referenceType;

        return $this->appCache->get($cacheId, function (ItemInterface $item) use ($pageTranslation, $referenceType) {
            $item->expiresAfter(43200); // 12 hours

            $site = $pageTranslation->getPage()->getSite();
            $routes = $this->router->getRouteCollection();

            $routeData = null;

            foreach ($routes as $name => $route) {
                $page = $pageTranslation->getPage();
                /** @var Route $route */
                if ($page instanceof App && $route->getDefault('_controller') === 'App\Controller\AppsController::app') {
                    $routeLocale = $route->getDefault('_locale');

                    $host = $site->getHost();
                    if ($route->getHost() && $route->getHost() != $host) {
                        continue;
                    }

                    if ($routeLocale === $pageTranslation->getLocale()) {
                        $routeData = [
                            'route'  => $route,
                            'name'   => $route->getDefault('_canonical_route'),
                            'params' => array_merge($route->getDefaults(), [
                                '_locale' => $routeLocale,
                                'ref'     => $page->getRef(),
                            ]),
                        ];

                        break;
                    }
                } elseif (strpos($route->getDefault('_controller'), 'App\Controller') === 0
                    && $route->getDefault('ref') === $pageTranslation->getPage()->getRef()) {
                    $routeLocale = $route->getDefault('_locale');

                    $host = $site->getHost();
                    if ($route->getHost() && $route->getHost() != $host) {
                        continue;
                    }

                    if ($routeLocale === $pageTranslation->getLocale()) {
                        $routeData = [
                            'route'  => $route,
                            'name'   => $route->getDefault('_canonical_route'),
                            'params' => array_merge($route->getDefaults(), [
                                '_locale' => $routeLocale,
                            ]),
                        ];

                        break;
                    }
                }
            }

            $data = null;

            if ($routeData) {
                return $this->router->generate($routeData['name'], $routeData['params'], $referenceType);
            }

            return null;
        }, $force ? INF : null);
    }

    public function getPageUrl($ref, $host, $locale = null)
    {
        $page = $this->pageRepository->findOneActiveByRefAndHost($ref, $host, $locale);
        if (!$page) {
            return null;
        }

        return $this->getUrl($page->getOneTranslation($locale));
    }

    public function getPageLinks($ref, $host, $locale = null)
    {
        $pageLinks = [];

        $page = $this->pageRepository->findOneActiveByRefAndHost($ref, $host);
        if ($page) {
            foreach ($page->getTranslations() as $pageTranslation) {
                if ($pageTranslation->getLocale() == $locale) {
                    continue;
                }

                $pageLinks[$pageTranslation->getLocale()] = $this->getUrl($pageTranslation);
            }
        }

        return $pageLinks;
    }
}
