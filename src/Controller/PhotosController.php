<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(host: '%photos_host%')]
class PhotosController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
    ) {}

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'photos_home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/home.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/fr/mentions-legales', 'en' => '/legal-mentions', 'de' => '/de/impressum'], name: 'photos_legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref)
    {
        return $this->commonController->legalMention($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/plan-du-site', 'en' => '/sitemap', 'de' => '/de/sitemap'], name: 'photos_sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'photos_sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route(path: ['fr' => '/fr/rss', 'en' => '/rss', 'de' => '/de/rss'], name: 'photos_rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    #[Route(path: ['fr' => '/fr/contact', 'en' => '/contact', 'de' => '/de/kontakt'], name: 'photos_contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/visualisez', 'en' => '/show', 'de' => '/de/anzeigen'], name: 'photos_show', defaults: ['ref' => 'show'])]
    public function show(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/show.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/fr/demonstration', 'en' => '/demo', 'de' => '/de/demonstration'], name: 'photos_demo', defaults: ['ref' => 'demo'])]
    public function demo(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/demo.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/fr/aide', 'en' => '/help', 'de' => '/de/hilfe'], name: 'photos_help', defaults: ['ref' => 'help'])]
    public function help(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/help.html.twig', ['page' => $page, 'showLinks' => true]);
    }
}
