<?php

namespace App\Services;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;

class SiteService
{
    /**
     * @var SiteRepository
     */
    protected $siteRepository;
    public function __construct(
        protected EntityManagerInterface $em,
        protected PageService $pageService,
        protected ArticleService $articleService,
        protected TranslatorInterface $translator,
        protected Environment $templating,
        protected CacheInterface $appCache
    )
    {
        $this->siteRepository = $em->getRepository(Site::class);
    }
    /**
     * Update a site.
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
     */
    public function remove(Site $site)
    {
        $this->em->remove($site);
        $this->em->flush();
    }
    public function searchQuery($filters = [])
    {
        return $this->siteRepository->createQueryBuilder('s');
    }
    /**
     * Search.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function getQueryForSearch($filters = [])
    {
        return $this->siteRepository->queryForSearch($filters);
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
     * @param string $host
     *
     * @return Site|null
     */
    public function findOneByHost($host)
    {
        return $this->siteRepository->findOneByHost($host);
    }
    /**
     * Find one by ref.
     *
     * @param string $ref
     *
     * @return Site|null
     */
    public function findOneByRef($ref)
    {
        return $this->siteRepository->findOneByRef($ref);
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
    public function getSitemap($host, $locale, $force = false)
    {
        $cacheId = 'sitemap-' . md5($host) .'-' . md5($locale);
        return $this->appCache->get($cacheId, function (ItemInterface $item) use ($locale) {
            $item->expiresAfter(43200);// 12 hours
            $sites = $this->findActives();
            $sitemap = [];
            foreach ($sites as $site) {
                $ref = $site->getRef();
                $host = $site->getHost();
                if (in_array($ref, ['me', 'freelance'])) {
                    continue;
                }
                $sitemap[$ref] = ['item' => ['host' => $host, 'ref' => 'home', 'label' => 'common.sitemap.site_' . $ref], 'children' => [['item' => ['label' => 'common.sitemap.login'], 'children' => [['item' => ['host' => $host, 'ref' => 'register']], ['item' => ['host' => $host, 'ref' => 'profile']]]]]];
                if ($ref == 'darkwood') {
                    $sitemap[$ref]['children'][] = ['item' => ['label' => 'common.sitemap.player'], 'children' => [['item' => ['host' => $host, 'ref' => 'play']], ['item' => ['host' => $host, 'ref' => 'chat']], ['item' => ['host' => $host, 'ref' => 'users']], ['item' => ['host' => $host, 'ref' => 'rules']], ['item' => ['host' => $host, 'ref' => 'guestbook']], ['item' => ['host' => $host, 'ref' => 'extra']]]];
                    $sitemap[$ref]['children'][] = ['item' => ['label' => 'common.sitemap.rank'], 'children' => [['item' => ['host' => $host, 'ref' => 'rank', 'label' => 'darkwood.menu.rank_general']], ['item' => ['host' => $host, 'ref' => 'rank', 'label' => 'darkwood.menu.rank_by_class']], ['item' => ['host' => $host, 'ref' => 'rank', 'label' => 'darkwood.menu.rank_daily_fight']]]];
                } elseif ($ref == 'apps') {
                    $children = [];
                    $apps = $this->pageService->findActives($locale, 'app');
                    foreach ($apps as $app) {
                        $children[] = ['item' => ['host' => $host, 'ref' => $app->getRef()]];
                    }
                    $sitemap[$ref]['children'][] = ['item' => ['label' => 'common.sitemap.apps'], 'children' => $children];
                } elseif ($ref == 'photos') {
                    $sitemap[$ref]['children'][] = ['item' => ['label' => 'common.sitemap.gallery'], 'children' => [['item' => ['host' => $host, 'ref' => 'show']], ['item' => ['host' => $host, 'ref' => 'demo']], ['item' => ['host' => $host, 'ref' => 'help']]]];
                } elseif ($ref == 'blog') {
                    $children = [];
                    $articles = $this->articleService->findActives($locale, 5);
                    foreach ($articles as $article) {
                        $children[] = ['item' => ['host' => $host, 'page_translation' => $article->getOneTranslation()]];
                    }
                    $sitemap[$ref]['children'][] = ['item' => ['label' => 'common.sitemap.articles'], 'children' => $children];
                }
            }
            $formatSitemap = function ($items) use (&$formatSitemap, $locale) {
                foreach ($items as $key => $child) {
                    if (!isset($child['children'])) {
                        $child['children'] = [];
                    }
                    $child['label'] = null;
                    $child['link'] = null;
                    if (isset($child['item']['host'], $child['item']['ref'])) {
                        $page = $this->pageService->findOneActiveByRefAndHost($child['item']['ref'], $child['item']['host']);
                        if ($page !== null) {
                            $pageTranslation = $page->getOneTranslation($locale);
                            if ($pageTranslation) {
                                $child['label'] = $pageTranslation->getTitle();
                                $child['link'] = $this->pageService->getUrl($pageTranslation, true);
                            }
                        }
                    } else if (isset($child['item']['host'], $child['item']['page_translation'])) {
                        $child['label'] = $child['item']['page_translation']->getTitle();
                        $child['link'] = $this->pageService->getUrl($child['item']['page_translation'], true);
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
        }, $force ? INF : null);
    }
    public function getSitemapXml($host, $locale)
    {
        $pages = $this->pageService->findActives($locale, null, $host);
        $urls = [];
        foreach ($pages as $page) {
            $pageTranslation = $page->getOneTranslation();
            $urls[] = ['loc' => $this->pageService->getUrl($pageTranslation, UrlGeneratorInterface::ABSOLUTE_URL), 'date' => $pageTranslation->getUpdated()];
        }
        return $this->templating->render('common/partials/sitemapXml.html.twig', ['urls' => $urls]);
    }
    public function getFeed($host, $locale)
    {
        $feed = [];
        $articles = $this->articleService->findActives($locale);
        foreach ($articles as $article) {
            $feed[] = ['type' => 'article', 'date' => $article->getCreated(), 'item' => $article];
        }
        usort($feed, function ($item1, $item2) {
            if ($item1['date'] == $item2['date']) {
                return 0;
            }
            return ($item1['date'] < $item2['date']) ? -1 : 1;
        });
        return $feed;
    }
    public function getRssXml($host, $locale)
    {
        $feed = $this->getFeed($host, $locale);
        return $this->templating->render('common/partials/rssXml.html.twig', ['feed' => $feed, 'locale' => $locale, 'host' => $host]);
    }
}
