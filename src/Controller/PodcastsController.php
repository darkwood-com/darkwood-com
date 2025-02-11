<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\BaserowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/', name: 'podcasts_', host: '%podcasts_host%')]
class PodcastsController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
		private readonly BaserowService $baserowService,
		private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {}

    private function fetchAndCachePodcasts(): array
    {
        return $this->cache->get('podcasts_items', function (ItemInterface $item) {
            $item->expiresAfter(86400); // Cache for 1 day

            $token = $this->baserowService->getBaserowToken();
            $baserowApiUrl = $this->baserowService->getHost() . '/api/applications/';
            $response = $this->httpClient->request('GET', $baserowApiUrl, [
                'headers' => [
                    'Authorization' => 'JWT ' . $token,
                ],
            ]);

            $databases = $response->toArray();
            $tableId = null;

            foreach ($databases as $database) {
                $table = array_filter($database['tables'], fn($table) => $table['name'] === 'Podcasts');
                if (!empty($table)) {
                    $tableId = reset($table)['id'];
                }
            }

            if ($tableId === null) {
                throw new \Exception('Podcasts table not found in Baserow.');
            }

            $baserowApiUrl = sprintf('%s/api/database/rows/table/%d/?%s', $this->baserowService->getHost(), $tableId, http_build_query([
                'filter__field_5982__contains' => 'published',
                'user_field_names' => 'true'
            ]));

            $response = $this->httpClient->request('GET', $baserowApiUrl, [
                'headers' => [
                    'Authorization' => 'JWT ' . $token,
                ],
            ]);

            return $response->toArray()['results'];
        });
    }

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $podcasts = $this->fetchAndCachePodcasts();

        return $this->render('podcasts/pages/home.html.twig', [
            'page' => $page,
            'podcasts' => $podcasts,
        ]);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

	#[Route('/apple_rss', name: 'podcast_apple_rss')]
    public function generateApplePodcastsRssFeed(): Response
    {
        $podcasts = $this->fetchAndCachePodcasts();

        // Generate the RSS feed
        $rssFeed = new \SimpleXMLElement('<rss version="2.0"></rss>');
        $channel = $rssFeed->addChild('channel');
        $channel->addChild('title', 'Darkwood Podcast');
        $channel->addChild('link', 'https://podcasts.darkwood.com');
        $channel->addChild('description', 'Darkwood Podcast');

        foreach ($podcasts as $podcast) {
            $rssItem = $channel->addChild('item');
            $rssItem->addChild('title', htmlspecialchars($podcast['title']));
            $rssItem->addChild('link', htmlspecialchars($podcast['audio_file']));
            $rssItem->addChild('description', htmlspecialchars($podcast['description']));
            $rssItem->addChild('pubDate', date(DATE_RSS, strtotime($podcast['creation_date'])));
        }

        $response = new Response($rssFeed->asXML());
        $response->headers->set('Content-Type', 'application/rss+xml');

        return $response;
    }
}
