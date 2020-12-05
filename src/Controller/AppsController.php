<?php

namespace App\Controller;

use App\Entity\App;
use App\Entity\AppContent;
use App\Entity\CommentPage;
use App\Form\CommentType;
use App\Services\CommentService;
use App\Services\PageService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/', name: 'apps_', host: '%apps_host%')]
class AppsController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private CommonController $commonController, private AuthenticationUtils $authenticationUtils, private TranslatorInterface $translator, private PaginatorInterface $paginator, private PageService $pageService, private CommentService $commentService)
    {
    }
    public function menu(Request $request, $ref, $entity)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        $apps = $this->pageService->findActives($request->getLocale(), 'app');
        $appLinks = [];
        foreach ($apps as $app) {
            $appLinks[] = ['label' => $app->getOneTranslation()->getTitle(), 'link' => $this->pageService->getUrl($app->getOneTranslation())];
        }
        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());
        return $this->render('apps/partials/menu.html.twig', ['last_username' => $lastUsername, 'csrf_token' => $csrfToken, 'appLinks' => $appLinks, 'pageLinks' => $pageLinks]);
    }
    #[Route(path: ['fr' => '/', 'en' => '/en', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        return $this->render('apps/pages/home.html.twig', ['page' => $page]);
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
    #[Route(path: ['fr' => '/article/{ref}/{slug}', 'en' => '/en/{ref}/{slug}', 'de' => '/de/{ref}/{slug}'], name: 'app', defaults: ['ref' => null, 'slug' => null])]
    public function app(Request $request, $ref, $slug = null)
    {
        $page = $this->commonController->getPage($request, $ref);
        $app = $page->getPage();
        if (!$app instanceof App) {
            throw $this->createNotFoundException('App not found !');
        }
        $contents = $app->getContents()->filter(function ($appContent) use ($request) {
            /* @var AppContent $appContent */
            return $appContent->getLocale() == $request->getLocale();
        });
        $content = $page->getContent();
        if (!is_null($slug)) {
            $content = $contents->filter(function ($appContent) use ($slug) {
                /* @var AppContent $appContent */
                return $appContent->getSlug() == $slug;
            })->current();
            if (!$content) {
                throw $this->createNotFoundException('App slug not found !');
            }
            $content = $content->getContent();
        }
        $comment = new CommentPage();
        $comment->setUser($this->getUser());
        $comment->setPage($page->getPage());
        $form = $this->createForm(CommentType::class, $comment);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($comment);
                $this->get('session')->getFlashBag()->add('success', $this->translator->trans('common.comment.submited'));
                return $this->redirect($this->generateUrl('apps_app', ['ref' => $ref, 'slug' => $slug]));
            }
        }
        $query = $this->commentService->findActiveCommentByPageQuery($page->getPage());
        $comments = $this->paginator->paginate($query, $request->query->get('page', 1), 10);
        return $this->render('apps/pages/app.html.twig', ['page' => $page, 'slug' => $slug, 'contents' => $contents, 'content' => $content, 'showLinks' => true, 'form' => $form->createView(), 'comments' => $comments]);
    }
}
