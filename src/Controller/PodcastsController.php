<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\BaserowService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function sprintf;

#[Route('/', name: 'podcasts_', host: '%podcasts_host%')]
class PodcastsController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
        private readonly BaserowService $baserowService,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {}

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route('/feed.rss', name: 'rss')]
    public function generateRssFeed(): Response
    {
        $podcasts = $this->fetchAndCachePodcasts();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');

        return $this->render('podcasts/pages/feed_rss.html.twig', [
            'podcasts' => $podcasts,
        ], $response);
    }

    #[Route(path: ['fr' => '/fr/{slug}', 'en' => '/{slug}', 'de' => '/de/{slug}'], name: 'home', defaults: ['ref' => 'home', 'slug' => null])]
    public function home(Request $request, $ref, ?string $slug = null): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $podcasts = $this->fetchAndCachePodcasts();
        $podcast = null;

        if ($slug !== null) {
            $podcast = array_filter($podcasts, static fn ($podcast) => $podcast['slug'] === $slug);
            if (empty($podcast)) {
                throw $this->createNotFoundException('Podcast not found');
            }
        }

        $podcastLinks = [
            'spotify' => 'https://open.spotify.com/show/0cUSC0ZhYFkDAXL7AvwJqD',
            'deezer' => 'https://www.deezer.com/fr/playlist/13537583503',
            'amazon_music' => 'https://music.amazon.fr/podcasts/f2a8b592-b204-4b6f-a8d0-0d38e67aaeaf/darkwood-podcast',
            'apple_podcasts' => 'https://podcastsconnect.apple.com/my-podcasts',
        ];

        return $this->render('podcasts/pages/home.html.twig', [
            'page' => $page,
            'podcast' => $podcast,
            'podcasts' => $podcasts,
            'podcastLinks' => $podcastLinks,
        ]);
    }

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
                $table = array_filter($database['tables'], static fn ($table) => $table['name'] === 'Podcasts');
                if (!empty($table)) {
                    $tableId = reset($table)['id'];
                }
            }

            if ($tableId === null) {
                throw new Exception('Podcasts table not found in Baserow.');
            }

            $baserowApiUrl = sprintf('%s/api/database/rows/table/%d/?%s', $this->baserowService->getHost(), $tableId, http_build_query([
                'filter__field_5982__contains' => 'published',
                'user_field_names' => 'true',
            ]));

            $response = $this->httpClient->request('GET', $baserowApiUrl, [
                'headers' => [
                    'Authorization' => 'JWT ' . $token,
                ],
            ]);

            return $response->toArray()['results'];
        });
    }
}
