<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[\Symfony\Component\Routing\Annotation\Route('/', name: 'freelance_', host: '%freelance_host%')]
class FreelanceController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(
        /**
         * @var CommonController
         */
        private \App\Controller\CommonController $commonController
    )
    {
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/', 'en' => '/en', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('freelance/pages/home.html.twig', ['page' => $page, 'showLinks' => true]);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/plan-du-site', 'en' => '/en/sitemap', 'de' => '/de/sitemap'], name: 'sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/sitemap.xml', 'en' => '/en/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(\Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/rss', 'en' => '/en/rss', 'de' => '/de/rss'], name: 'rss')]
    public function rss(\Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->commonController->rss($request);
    }
    #[\Symfony\Component\Routing\Annotation\Route(path: ['fr' => '/contact', 'en' => '/en/contact', 'de' => '/de/kontakt'], name: 'contact', defaults: ['ref' => 'contact'])]
    public function contact(\Symfony\Component\HttpFoundation\Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }
}
