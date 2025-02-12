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

	#[Route('/feed.rss', name: 'rss')]
    public function generateRssFeed(): Response
    {
        $podcasts = $this->fetchAndCachePodcasts();

        // Create the RSS feed
        $rssFeed = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:googleplay="http://www.google.com/schemas/play-podcasts/1.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:media="http://search.yahoo.com/mrss/"></rss>');

        // Add XML stylesheet
        //$rssFeed->addProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="/global/feed/rss.xslt"');

        $channel = $rssFeed->addChild('channel');
        $channel->addChild('title', 'Darkwood Podcast');
        $channel->addChild('link', 'https://podcasts.darkwood.com');
        $channel->addChild('description', 'Darkwood Podcast');
        $channel->addChild('ttl', '60');
        $channel->addChild('generator', 'darkwood.com');
        $channel->addChild('language', 'fr');
        $channel->addChild('copyright', 'Darkwood');
        $channel->addChild('itunes:keywords', 'podcast, darkwood');
        $channel->addChild('itunes:author', 'Darkwood');
        $channel->addChild('itunes:subtitle', 'Darkwood Podcast Subtitle');
        $channel->addChild('itunes:summary', 'Darkwood Podcast Summary');
        $channel->addChild('itunes:explicit', 'false');

        $itunesOwner = $channel->addChild('itunes:owner', null);
        $itunesOwner->addChild('itunes:name', 'Darkwood');
        $itunesOwner->addChild('itunes:email', 'matyo91@gmail.com');

        $channel->addChild('itunes:type', 'serial');
        $channel->addChild('itunes:image', null)->addAttribute('href', 'https://darkwood.com/common/images/site/cover.png');

        foreach ($podcasts as $podcast) {
            $rssItem = $channel->addChild('item');
            $rssItem->addChild('title', htmlspecialchars($podcast['title']));
            $rssItem->addChild('itunes:title', htmlspecialchars($podcast['title']));
            $rssItem->addChild('link', htmlspecialchars($podcast['audio_file']));
            $rssItem->addChild('description', htmlspecialchars($podcast['description']));
            $rssItem->addChild('itunes:summary', htmlspecialchars($podcast['description']));
            $rssItem->addChild('pubDate', date(DATE_RSS, strtotime($podcast['creation_date'])));
            //$rssItem->addChild('itunes:duration', '00:00:00'); // Placeholder duration
            //$rssItem->addChild('enclosure', null)->addAttribute('url', htmlspecialchars($podcast['audio_file']))->addAttribute('type', 'audio/mpeg');
            $rssItem->addChild('guid', (string) $podcast['id'])->addAttribute('isPermaLink', 'false');
            $rssItem->addChild('itunes:explicit', 'false');
        }

        $response = new Response($rssFeed->asXML());
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');

        return $response;
    }
}
