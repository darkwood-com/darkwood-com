<?php

namespace App\Services;

use App\Entity\Page;
use App\Services\ArticleService;
use App\Services\BaseService;
use App\Services\NewsService;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class SiteService
 *
 * Object manager of site.
 */
class SiteService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @var PageService
     */
    protected $pageService;

    /**
     * @var ArticleService
     */
    protected $articleService;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Environment
     */
    protected $templating;

    public function __construct(
        EntityManagerInterface $em,
        PageService $pageService,
        ArticleService $articleService,
        TranslatorInterface $translator,
        Environment $templating
    ) {
        $this->em = $em;
        $this->siteRepository = $em->getRepository(Site::class);
        $this->pageService = $pageService;
        $this->articleService = $articleService;
        $this->translator = $translator;
        $this->templating = $templating;
    }

    /**
     * Update a site.
     *
     * @param Site $site
     *
     * @return Site
     */
    public function save(Site $site)
    {
        $site->setUpdated(new \DateTime('now'));
        $this->em->persist($site);
        $this->em->flush();

        return $site;
    }

    /**
     * Remove one site.
     *
     * @param Site $site
     */
    public function remove(Site $site)
    {
        $this->em->remove($site);
        $this->em->flush();
    }

    public function searchQuery($filters = array())
    {
        return $this->siteRepository->createQueryBuilder( 's');
    }

    /**
     * Search.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function getQueryForSearch($filters = array(), $state = null)
    {
        return $this->siteRepository->queryForSearch($filters, $state);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        return $this->siteRepository->findOneToEdit($id);
    }

    /**
     * Find one by host.
     *
     * @param $host
     *
     * @return Site|null
     */
    public function findOneByHost($host)
    {
        return $this->siteRepository->findOneByHost($host);
    }

    /**
     * Find all.
     *
     * @return mixed
     */
    public function findAll()
    {
        return $this->siteRepository->findAll();
    }

    /**
     * @return Site[]
     */
    public function findActives()
    {
        return $this->siteRepository->findActives();
    }

    public function getSitemap($locale)
    {
        $sites = $this->findActives();

        $sitemap = array();
        foreach ($sites as $site) {
            $ref = $site->getRef();
            $host = $site->getHost();

            if (in_array($ref, array('me', 'blog'))) {
                continue;
            }

            $sitemap[$ref] = array(
                'item' => array('host' => $host, 'ref' => 'home', 'label' => 'common.sitemap.site_'.$ref),
                'children' => array(
                    array(
                        'item' => array('label' => 'common.sitemap.login'),
                        'children' => array(
                            array('item' => array('host' => $host, 'ref' => 'register')),
                            array('item' => array('host' => $host, 'ref' => 'profile')),
                        ),
                    ),
                ),
            );

            if ($ref == 'darkwood') {
                $sitemap[$ref]['children'][] = array(
                    'item' => array('label' => 'common.sitemap.player'),
                    'children' => array(
                        array('item' => array('host' => $host, 'ref' => 'play')),
                        array('item' => array('host' => $host, 'ref' => 'chat')),
                        array('item' => array('host' => $host, 'ref' => 'users')),
                        array('item' => array('host' => $host, 'ref' => 'rules')),
                        array('item' => array('host' => $host, 'ref' => 'guestbook')),
                        array('item' => array('host' => $host, 'ref' => 'extra')),
                    ),
                );
                $sitemap[$ref]['children'][] = array(
                    'item' => array('label' => 'common.sitemap.rank'),
                    'children' => array(
                        array('item' => array('host' => $host, 'ref' => 'rank', 'label' => 'darkwood.menu.rank_general')),
                        array('item' => array('host' => $host, 'ref' => 'rank', 'label' => 'darkwood.menu.rank_by_class')),
                        array('item' => array('host' => $host, 'ref' => 'rank', 'label' => 'darkwood.menu.rank_daily_fight')),
                    ),
                );
            } elseif ($ref == 'apps') {
                $children = array();
                $apps = $this->pageService->findActives($locale, 'app');
                foreach ($apps as $app) {
                    $children[] = array('item' => array('host' => $host, 'ref' => $app->getRef()));
                }

                $sitemap[$ref]['children'][] = array(
                    'item' => array('label' => 'common.sitemap.apps'),
                    'children' => $children,
                );
            } elseif ($ref == 'photos') {
                $sitemap[$ref]['children'][] = array(
                    'item' => array('label' => 'common.sitemap.gallery'),
                    'children' => array(
                        array('item' => array('host' => $host, 'ref' => 'show')),
                        array('item' => array('host' => $host, 'ref' => 'demo')),
                        array('item' => array('host' => $host, 'ref' => 'help')),
                    ),
                );
            }
        }

        $formatSitemap = function ($items) use (&$formatSitemap, $locale) {
            foreach ($items as $key => $child) {
                if (!isset($child['children'])) {
                    $child['children'] = array();
                }
                $child['label'] = null;
                $child['link'] = null;
                if (isset($child['item']['host'], $child['item']['ref'])) {
                    $page = $this->pageService
                        ->findOneActiveByRefAndHost($child['item']['ref'], $child['item']['host']);
                    if ($page) {
                        $pageTranslation = $page->getOneTranslation($locale);
                        if ($pageTranslation) {
                            $child['label'] = $pageTranslation->getTitle();
                            $child['link'] = $this->pageService->getUrl($pageTranslation, true);
                        }
                    }
                }
                if (isset($child['item']['label'])) {
                    $child['label'] = $this->translator->trans($child['item']['label']);
                }

                $child['children'] = $formatSitemap($child['children']);

                $items[$key] = $child;
            }

            return $items;
        };

        return $formatSitemap($sitemap);
    }

    public function getSitemapXml($host, $locale)
    {
        $pages = $this->pageService->findActives($locale, null, $host);

        $urls = array();
        foreach ($pages as $page) {
            $pageTranslation = $page->getOneTranslation();

            $urls[] = array(
                'loc' => $this->pageService->getUrl($pageTranslation),
                'date' => $pageTranslation->getUpdated(),
            );
        }

        return $this->templating->render('common/partials/sitemapXml.html.twig', array(
            'urls' => $urls,
        ));
    }

    public function getFeed($host, $locale)
    {
        $feed = array();

        $articles = $this->articleService->findActives($locale);
        foreach($articles as $article)
        {
            $feed[] = array(
                'type' => 'article',
                'date' => $article->getCreated(),
                'item' => $article
            );
        }

        usort($feed, function($item1, $item2) {
            return $item1['date'] < $item2['date'];
        });

        return $feed;
    }

    public function getRssXml($host, $locale)
    {
        $feed = $this->getFeed($host, $locale);

        return $this->templating->render('common/partials/rssXml.html.twig', array(
            'feed' => $feed,
            'locale' => $locale,
            'host' => $host,
        ));
    }
}
