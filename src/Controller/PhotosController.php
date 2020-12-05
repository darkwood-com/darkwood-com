<?php

namespace App\Controller;

use App\Services\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/', name: 'photos_', host: '%photos_host%')]
class PhotosController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private CommonController $commonController, private AuthenticationUtils $authenticationUtils, private PageService $pageService)
    {
    }
    public function menu(Request $request, $ref, $entity)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());
        return $this->render('photos/partials/menu.html.twig', ['last_username' => $lastUsername, 'csrf_token' => $csrfToken, 'pageLinks' => $pageLinks]);
    }
    #[Route(path: ['fr' => '/', 'en' => '/en', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/home.html.twig', ['page' => $page]);
    }
    #[Route(path: ['fr' => '/plan-du-site', 'en' => '/en/sitemap', 'de' => '/de/sitemap'], name: 'sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }
    #[Route(path: ['fr' => '/sitemap.xml', 'en' => '/en/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }
    #[Route(path: ['fr' => '/rss', 'en' => '/en/rss', 'de' => '/de/rss'], name: 'rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }
    #[Route(path: ['fr' => '/contact', 'en' => '/en/contact', 'de' => '/de/kontakt'], name: 'contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }
    #[Route(path: ['fr' => '/visualisez', 'en' => '/en/show', 'de' => '/de/anzeigen'], name: 'show', defaults: ['ref' => 'show'])]
    public function show(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/show.html.twig', ['page' => $page]);
    }
    #[Route(path: ['fr' => '/demonstration', 'en' => '/en/demo', 'de' => '/de/demonstration'], name: 'demo', defaults: ['ref' => 'demo'])]
    public function demo(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/demo.html.twig', ['page' => $page]);
    }
    #[Route(path: ['fr' => '/aide', 'en' => '/en/help', 'de' => '/de/hilfe'], name: 'help', defaults: ['ref' => 'help'])]
    public function help(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/help.html.twig', ['page' => $page, 'showLinks' => true]);
    }
}
