<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="me_", host="%me_host%")
 */
class MeController extends AbstractController
{
    /**
     * @var CommonController
     */
    private $commonController;

    public function __construct(
        CommonController $commonController
    ) {
        $this->commonController = $commonController;
    }

    /**
     * @Route({ "fr": "/", "en": "/en", "de": "/de" }, name="home", defaults={"ref": "home"})
     */
    public function home(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('me/pages/home.html.twig', [
            'page'      => $page,
            'showLinks' => true,
            'cv'        => true,
        ]);
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
}
