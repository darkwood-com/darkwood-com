<?php

namespace App\Services;

use App\Entity\App;
use App\Services\BaseService;
use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\Site;
use App\Repository\PageRepository;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
     * @var pageRepository
     */
    protected $pageRepository;

    /**
     * @var CacheInterface
     */
    protected $appCache;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(
        EntityManagerInterface $em,
        CacheInterface $appCache,
        RouterInterface $router
    ) {
        $this->em = $em;
        $this->pageRepository = $em->getRepository(Page::class);
        $this->appCache = $appCache;
        $this->router = $router;
    }

    /**
     * Update a pageTranslation.
     *
     * @param Page $page
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

    /**
     * Update a pageTranslation.
     *
     * @param PageTranslation $pageTranslation
     *
     * @return PageTranslation
     */
    public function saveTranslation(PageTranslation $pageTranslation, $invalidate = false)
    {
        $pageTranslation->setUpdated(new \DateTime('now'));
        $this->em->persist($pageTranslation);
        $this->em->flush();

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
     * @return null|object
     */
    public function findOneBy($filters = array())
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
    public function getQueryForSearch($filters = array(), $type, $host, $locale, $order = 'normal')
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
     * @return null|PageTranslation
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
     * @param PageTranslation $pageTranslation
     * @param bool            $absolute
     *
     * @return mixed
     */
    public function getUrl(PageTranslation $pageTranslation, $absolute = false, $force = false)
    {
        $cacheId = 'page_url-'.$pageTranslation->getId().'-'.($absolute ? '1' : 0);
        return $this->appCache->get($cacheId, function (ItemInterface $item) use ($pageTranslation) {
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
                        $routeData = array(
                            'route' => $route,
                            'name' => $route->getDefault('_canonical_route'),
                            'params' => array_merge($route->getDefaults(), array(
                                '_locale' => $routeLocale,
                                'ref' => $page->getRef(),
                            )),
                        );

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
                        $routeData = array(
                            'route' => $route,
                            'name' => $route->getDefault('_canonical_route'),
                            'params' => array_merge($route->getDefaults(), array(
                                '_locale' => $routeLocale,
                            )),
                        );

                        break;
                    }
                }
            }

            $data = null;

            if ($routeData) {
                return $this->router->generate($routeData['name'], $routeData['params'], UrlGeneratorInterface::NETWORK_PATH);
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
        $pageLinks = array();

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
