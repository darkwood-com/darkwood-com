<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ArticleTranslation;
use App\Entity\CommentArticle;
use App\Form\CommentType;
use App\Service\BlogArticleService;
use App\Service\CommentService;
use App\Service\DarkwoodEntitlementService;
use App\Service\PageService;
use App\Validator\Constraints\PaginationDTO;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/', name: 'blog_', host: '%blog_host%')]
class BlogController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly TranslatorInterface $translator,
        private readonly PaginatorInterface $paginator,
        private readonly PageService $pageService,
        private readonly BlogArticleService $articleService,
        private readonly CommentService $commentService,
        private readonly DarkwoodEntitlementService $entitlementService,
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly RequestStack $requestStack
    ) {}

    public function menu(Request $request, $ref, $entity)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->tokenManager->getToken('authenticate')->getValue();
        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());
        $currentRoute = $this->requestStack->getMainRequest()?->attributes->get('_route');
        $activeRoute = $currentRoute;

        if ('blog_article' === $currentRoute && $entity instanceof ArticleTranslation) {
            $activeRoute = $entity->getArticle()->getType()->blogListRouteName();
        }

        return $this->render('blog/partials/menu.html.twig', ['last_username' => $lastUsername, 'csrf_token' => $csrfToken, 'pageLinks' => $pageLinks, 'currentRoute' => $currentRoute, 'activeRoute' => $activeRoute]);
    }

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, #[MapQueryString] ?PaginationDTO $pagination, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $query = $this->articleService->findActivesQueryBuilder($request->getLocale());
        $request->query->set('sort', $pagination?->sort ?? '');

        $articles = $this->paginator->paginate($query, $pagination?->page ?? 1, 10);
        $lastAutoArticle = $this->articleService->findLatestAutoArticle($request->getLocale());

        return $this->render('blog/pages/home.html.twig', [
            'page' => $page,
            'articles' => $articles,
            'lastAutoArticle' => $lastAutoArticle,
            'showLinks' => true,
        ]);
    }

    #[Route(path: ['fr' => '/fr/auto', 'en' => '/auto', 'de' => '/de/auto'], name: 'auto', defaults: ['ref' => 'auto'])]
    public function auto(Request $request, #[MapQueryString] ?PaginationDTO $pagination, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $query = $this->articleService->findAutoActivesQueryBuilder($request->getLocale());
        $articles = $this->paginator->paginate($query, $pagination?->page ?? 1, 10);

        return $this->render('blog/pages/home.html.twig', [
            'page' => $page,
            'articles' => $articles,
            'lastAutoArticle' => null,
            'showLinks' => true,
        ]);
    }

    #[Route(path: ['fr' => '/fr/mentions-legales', 'en' => '/legal-mentions', 'de' => '/de/impressum'], name: 'legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref)
    {
        return $this->commonController->legalMention($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/plan-du-site', 'en' => '/sitemap', 'de' => '/de/sitemap'], name: 'sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route(path: ['fr' => '/fr/rss', 'en' => '/rss', 'de' => '/de/rss'], name: 'rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    #[Route(path: ['fr' => '/fr/contact', 'en' => '/contact', 'de' => '/de/kontakt'], name: 'contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/article/{slug}', 'en' => '/article/{slug}', 'de' => '/de/article/{slug}'], name: 'article', defaults: ['ref' => 'article', 'slug' => null])]
    public function article(Request $request, $ref, $slug)
    {
        if ($request->query->get('sort') && $request->query->get('sort') !== 'a.created') {
            throw $this->createNotFoundException('Sort query is not allowed');
        }

        $page = $this->commonController->getPage($request, $ref);
        $article = $this->articleService->findOneBySlug($slug, $request->getLocale());
        if (!$article) {
            throw $this->createNotFoundException('Article not found !');
        }

        $comment = new CommentArticle();
        $comment->setUser($this->getUser());
        $comment->setPage($page->getPage());
        $comment->setArticle($article);

        $form = $this->createForm(CommentType::class, $comment);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($comment);
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('success', $this->translator->trans('common.comment.submited'));

                return $this->redirectToRoute('blog_article', ['slug' => $article->getOneTranslation($request->getLocale())->getSlug()]);
            }
        }

        $query = $this->commentService->findActiveCommentByArticleQuery($article);
        $comments = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 10);

        $isPremiumUser = $this->entitlementService->isPremium($this->getUser());

        return $this->render('blog/pages/article.html.twig', ['page' => $page, 'article' => $article, 'entity' => $article->getOneTranslation($request->getLocale()), 'showLinks' => true, 'form' => $form, 'comments' => $comments, 'isPremiumUser' => $isPremiumUser]);
    }
}
