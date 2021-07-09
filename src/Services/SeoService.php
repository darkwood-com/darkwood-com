<?php

namespace App\Services;

use App\Entity\App;
use App\Entity\Article;
use App\Entity\PageTranslation;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class SeoService
 *
 * Object manager of site.
 */
class SeoService
{
    public function __construct(
        protected CacheInterface $appCache,
        protected RouterInterface $router,
        protected UploaderHelper $uploaderHelper
    ) {
    }

    public function getSeo($context, $force = false)
    {
        /** @var PageTranslation $pageTranslation */
        $pageTranslation = $context['page'];
        $cacheId         = 'seo-' . $pageTranslation->getId();
        if (isset($context['article']) && $context['article'] instanceof Article) {
            $articleTranslation = $context['article']->getOneTranslation($pageTranslation->getLocale());
            $cacheId .= '-article_' . $articleTranslation->getId();
        }

        return $this->appCache->get($cacheId, function (ItemInterface $item) use ($context) {
            $item->expiresAfter(43200); // 12 hours
            /** @var PageTranslation $pageTranslation */
            $pageTranslation = $context['page'];
            $data = [
                'title'       => $pageTranslation->getSeoTitle() ?? $pageTranslation->getTitle(),
                'description' => $pageTranslation->getSeoDescription() ?? $pageTranslation->getDescription(),
                'keywords'    => $pageTranslation->getSeoKeywords(),
                'facebook'    => [
                    'title'       => $pageTranslation->getOgTitle() ?? $pageTranslation->getTitle(),
                    'description' => $pageTranslation->getOgDescription() ?? $pageTranslation->getDescription(),
                    'type'        => $pageTranslation->getOgType() ?? 'article',
                    'url'         => '',
                    'site_name'   => $pageTranslation->getPage()->getSite()->getName(),
                    'src'         => $this->uploaderHelper->asset($pageTranslation, 'ogImage') ?? $this->uploaderHelper->asset($pageTranslation, 'image')
                ],
                'twitter' => [
                    'card'        => $pageTranslation->getTwitterCard() ?? 'summary',
                    'title'       => $pageTranslation->getTwitterTitle() ?? $pageTranslation->getTitle(),
                    'description' => $pageTranslation->getTwitterDescription() ?? $pageTranslation->getDescription(),
                    'site'        => $pageTranslation->getTwitterSite(),
                    'src'         => $this->uploaderHelper->asset($pageTranslation, 'twitterImage') ?? $this->uploaderHelper->asset($pageTranslation, 'image')
                ]
                ];
            $page = $pageTranslation->getPage();
            if ($page instanceof App) {
                $data = array_replace_recursive($data, ['facebook' => ['src' => $this->uploaderHelper->asset($page, 'banner')], 'twitter' => ['src' => $this->uploaderHelper->asset($page, 'banner')]]);
            }
            if (isset($context['article']) && $context['article'] instanceof Article) {
                $articleTranslation = $context['article']->getOneTranslation($pageTranslation->getLocale());
                $data = array_replace_recursive($data, ['title' => $articleTranslation->getTitle(), 'facebook' => ['src' => $this->uploaderHelper->asset($articleTranslation, 'image'), 'title' => $articleTranslation->getTitle()], 'twitter' => ['src' => $this->uploaderHelper->asset($articleTranslation, 'image'), 'title' => $articleTranslation->getTitle()]]);
            }

            return $data;
        }, $force ? INF : null);
    }
}
