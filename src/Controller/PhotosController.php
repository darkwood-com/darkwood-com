<?php

namespace App\Controller;

use App\Services\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/", name="photos_", host="%photos_host%")
 */
class PhotosController extends AbstractController
{
    /**
     * @var CommonController
     */
    private $commonController;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var PageService
     */
    private $pageService;

    public function __construct(
        CommonController $commonController,
        AuthenticationUtils $authenticationUtils,
        PageService $pageService
    )
    {
        $this->commonController = $commonController;
        $this->authenticationUtils = $authenticationUtils;
        $this->pageService = $pageService;
    }

    public function menu(Request $request, $ref)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        $pageLinks = $this->pageService->getPageLinks($ref, $request->getHost(), $request->getLocale());

        return $this->render('photos/partials/menu.html.twig', array(
            'last_username' => $lastUsername,
            'csrf_token' => $csrfToken,
            'pageLinks' => $pageLinks,
        ));
    }

    /**
     * @Route({ "fr": "/", "en": "/en", "de": "/de" }, name="home", defaults={"ref": "home"})
     */
    public function home(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/home.html.twig', array(
            'page' => $page,
        ));
    }

    /**
     * @Route({ "fr": "/plan-du-site", "en": "/en/sitemap", "de": "/de/sitemap" }, name="sitemap", defaults={"ref": "sitemap"})
     */
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    /**
     * @Route({ "fr": "/sitemap.xml", "en": "/en/sitemap.xml", "de": "/de/sitemap.xml" }, name="sitemap_xml")
     */
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    /**
     * @Route({ "fr": "/rss", "en": "/en/rss", "de": "/de/rss" }, name="rss")
     */
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    /**
     * @Route({ "fr": "/contact", "en": "/en/contact", "de": "/de/kontakt" }, name="contact", defaults={"ref": "contact"})
     */
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }

    /**
     * @Route({ "fr": "/visualisez", "en": "/en/show", "de": "/de/anzeigen" }, name="show", defaults={"ref": "show"})
     */
    public function show(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/show.html.twig', array(
            'page' => $page,
        ));
    }

    /**
     * @Route({ "fr": "/demonstration", "en": "/en/demo", "de": "/de/demonstration" }, name="demo", defaults={"ref": "demo"})
     */
    public function demo(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/demo.html.twig', array(
            'page' => $page,
        ));
    }

    /**
     * @Route({ "fr": "/aide", "en": "/en/help", "de": "/de/hilfe" }, name="help", defaults={"ref": "help"})
     */
    public function help(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('photos/pages/help.html.twig', array(
            'page' => $page,
            'showLinks' => true,
        ));
    }
}
