<?php

namespace App\Services;

use App\Services\BaseService;
use App\Entity\PageTranslation;
use Doctrine\Common\Cache\Cache;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class SeoService
 *
 * Object manager of site.
 */
class SeoService
{
    /**
     * @var CacheInterface
     */
    protected $appCache;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var UploaderHelper
     */
    protected $uploaderHelper;

    public function __construct(
        CacheInterface $appCache,
        RouterInterface $router,
        UploaderHelper $uploaderHelper
    ) {
        $this->appCache = $appCache;
        $this->router = $router;
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * @param PageTranslation $entity
     * @param bool $force
     * @return mixed
     */
    public function getSeo($entity, $force = false)
    {
        $ttl = $this->cacheService->data('router');
        $cacheId = 'seo-' . get_class($entity) . '-' . $entity->getId();

        $data = $this->cache->fetch($cacheId);
        if ($force || false === $data) {
            $data = null;

            if($entity instanceof PageTranslation) {
                $data = array(
                    'title' => $entity->getTitle(),
                    'description' => $entity->getDescription(),
                    'keywords' => $entity->getSeoKeywords(),
                    'twitter' => array(
                        'card' => ($entity->getTwitterCard() ? $entity->getTwitterCard() : 'summary'),
                        'title' => ($entity->getTwitterTitle() != '') ? $entity->getTwitterTitle() : $entity->getTitle(),
                        'description' => ($entity->getTwitterDescription() != '') ? $entity->getTwitterDescription() : $entity->getDescription(),
                        'site' => $entity->getTwitterSite(),
                        'src' => $this->uploaderHelper->asset($entity, 'twitterImage'),
                    ),
                    'facebook' => array(
                        'title' => ($entity->getOgTitle() != '') ? $entity->getOgTitle() : $entity->getTitle(),
                        'description' => ($entity->getOgDescription() != '') ? $entity->getOgDescription() : $entity->getDescription(),
                        'type' => ($entity->getOgType() ? $entity->getOgType() : 'article'),
                        'src' => $this->uploaderHelper->asset($entity, 'ogImage'),
                        'url' => '',
                    )
                );
            }

            $this->cache->save($cacheId, $data, $ttl);
        }

        return $data;
    }
}
