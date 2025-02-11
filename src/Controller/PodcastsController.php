<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\BaserowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/', name: 'podcasts_', host: '%podcasts_host%')]
class PodcastsController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
		private readonly BaserowService $baserowService,
		private readonly HttpClientInterface $httpClient,
    ) {}

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('podcasts/pages/home.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

	#[Route('/apple_rss', name: 'podcast_apple_rss')]
    public function generateApplePodcastsRssFeed(): Response
    {
        $token = $this->baserowService->getBaserowToken();

		$baserowApiUrl = $this->baserowService->getHost() . '/api/database/databases/';
        $response = $this->httpClient->request('GET', $baserowApiUrl, [
            'headers' => [
                'Authorization' => 'Token ' . $token,
            ],
        ]);

        $databases = $response->toArray();

		return $this->json(['databases' => $databases]);
        $tableId = null;

        foreach ($tables as $table) {
            if ($table['name'] === 'Podcasts') {
                $tableId = $table['id'];
                break;
            }
        }

        if ($tableId === null) {
            throw new \Exception('Podcasts table not found in Baserow.');
        }

		return $this->json(['tableId' => $tableId]);


		$baserowApiUrl = $this->baserowService->getHost() . '/api/database/rows/table/1/?user_field_names=true';

        $response = $this->httpClient->request('GET', $baserowApiUrl, [
            'headers' => [
                'Authorization' => 'Token ' . $token,
            ],
        ]);

        $items = $response->toArray()['results'];

        // Generate the RSS feed
        $rssFeed = new \SimpleXMLElement('<rss version="2.0"></rss>');
        $channel = $rssFeed->addChild('channel');
        $channel->addChild('title', 'My Podcast');
        $channel->addChild('link', 'https://yourwebsite.com/podcast');
        $channel->addChild('description', 'This is my podcast RSS feed.');

        foreach ($items as $item) {
            $rssItem = $channel->addChild('item');
            $rssItem->addChild('title', htmlspecialchars($item['title']));
            $rssItem->addChild('link', htmlspecialchars($item['link']));
            $rssItem->addChild('description', htmlspecialchars($item['description']));
            $rssItem->addChild('pubDate', date(DATE_RSS, strtotime($item['pub_date'])));
        }

        $response = new Response($rssFeed->asXML());
        $response->headers->set('Content-Type', 'application/rss+xml');

        return $response;
    }
}
