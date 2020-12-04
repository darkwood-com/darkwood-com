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
class PhotosController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
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
    public function __construct(\App\Controller\CommonController $commonController, \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils, \App\Services\PageService $pageService)
    {
        $this->commonController = $commonController;
        $this->authenticationUtils = $authenticationUtils;
        $this->pageService = $pageService;
    }
    public function menu(\Symfony\Component\HttpFoundation\Request $request, $ref, $entity)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());
        return $this->render('photos/partials/menu.html.twig', ['last_username' => $lastUsername, 'csrf_token' => $csrfToken, 'pageLinks' => $pageLinks]);
    }
    /**
     * @Route({ "fr": "/", "en": "/en", "de": "/de" }, name="home", defaults={"ref": "home"})
     */
    public function home(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/home.html.twig', ['page' => $page]);
    }
    /**
     * @Route({ "fr": "/plan-du-site", "en": "/en/sitemap", "de": "/de/sitemap" }, name="sitemap", defaults={"ref": "sitemap"})
     */
    public function sitemap(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }
    /**
     * @Route({ "fr": "/sitemap.xml", "en": "/en/sitemap.xml", "de": "/de/sitemap.xml" }, name="sitemap_xml")
     */
    public function sitemapXml(\Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }
    /**
     * @Route({ "fr": "/rss", "en": "/en/rss", "de": "/de/rss" }, name="rss")
     */
    public function rss(\Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->commonController->rss($request);
    }
    /**
     * @Route({ "fr": "/contact", "en": "/en/contact", "de": "/de/kontakt" }, name="contact", defaults={"ref": "contact"})
     */
    public function contact(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }
    /**
     * @Route({ "fr": "/visualisez", "en": "/en/show", "de": "/de/anzeigen" }, name="show", defaults={"ref": "show"})
     */
    public function show(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/show.html.twig', ['page' => $page]);
    }
    /**
     * @Route({ "fr": "/demonstration", "en": "/en/demo", "de": "/de/demonstration" }, name="demo", defaults={"ref": "demo"})
     */
    public function demo(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/demo.html.twig', ['page' => $page]);
    }
    /**
     * @Route({ "fr": "/aide", "en": "/en/help", "de": "/de/hilfe" }, name="help", defaults={"ref": "help"})
     */
    public function help(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('photos/pages/help.html.twig', ['page' => $page, 'showLinks' => true]);
    }
}
