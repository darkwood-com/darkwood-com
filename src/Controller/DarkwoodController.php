<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommentPage;
use App\Form\CommentType;
use App\Services\ArticleService;
use App\Services\CommentService;
use App\Services\GameService;
use App\Services\PageService;
use App\Services\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

use function in_array;

#[Route('/', name: 'darkwood_', host: '%darkwood_host%', priority: -1)]
class DarkwoodController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(
        private CommonController $commonController,
        private ArticleService $articleService,
        private AuthenticationUtils $authenticationUtils,
        private TranslatorInterface $translator,
        private PaginatorInterface $paginator,
        private PageService $pageService,
        private CommentService $commentService,
        private UserService $userService,
        private GameService $gameService,
        private CsrfTokenManagerInterface $tokenManager
    ) {
    }

    public function menu(Request $request, $ref, $entity)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->tokenManager->getToken('authenticate')->getValue();
        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());

        return $this->render('darkwood/partials/menu.html.twig', ['last_username' => $lastUsername, 'csrf_token' => $csrfToken, 'pageLinks' => $pageLinks]);
    }

    #[Route(path: ['fr' => '/', 'en' => '/en', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref): \Symfony\Component\HttpFoundation\Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $articles = $this->articleService->findActives($request->getLocale(), 5);

        return $this->render('darkwood/pages/home.html.twig', ['page' => $page, 'news' => $articles, 'showLinks' => true]);
    }

    #[Route(path: ['fr' => '/mentions-legales', 'en' => '/en/legal-mentions', 'de' => '/de/impressum'], name: 'legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref)
    {
        return $this->commonController->legalMention($request, $ref);
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

    #[Route(path: ['fr' => '/news/{slug}', 'en' => '/en/news/{slug}', 'de' => '/de/news/{slug}'], name: 'news', defaults: ['ref' => 'news', 'slug' => null])]
    public function news(Request $request, $ref, $slug): \Symfony\Component\HttpFoundation\Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $news = $this->articleService->findOneBySlug($slug, $request->getLocale());
        if (!$news) {
            throw $this->createNotFoundException('News not found !');
        }

        return $this->render('darkwood/pages/news.html.twig', ['page' => $page, 'news' => $news, 'showLinks' => true]);
    }

    #[Route(path: ['fr' => '/jouer/{display}', 'en' => '/en/play/{display}', 'de' => '/de/spiel/{display}'], name: 'play', defaults: ['ref' => 'play', 'display' => null])]
    public function play(Request $request, $ref = 'play', $display = null)
    {
        $page = $this->commonController->getPage($request, $ref);
        $parameters = $this->gameService->play($request, $this->getUser(), $display);
        if ($parameters instanceof \Symfony\Component\HttpFoundation\Response) {
            return $parameters;
        }

        $parameters['page'] = $page;
        if ($request->isXmlHttpRequest()) {
            return $this->render('darkwood/partials/play/' . $parameters['display'] . '/' . $parameters['state'] . '.html.twig', $parameters);
        }

        return $this->render('darkwood/pages/play.html.twig', $parameters);
    }

    #[Route(path: ['fr' => '/chat', 'en' => '/en/chat', 'de' => '/de/chat'], name: 'chat', defaults: ['ref' => 'chat'])]
    public function chat(Request $request, $ref)
    {
        if ($request->get('sort') && $request->get('sort') !== 'c.created') {
            throw $this->createNotFoundException('Sort query is not allowed');
        }

        $page = $this->commonController->getPage($request, $ref);
        $comment = new CommentPage();
        $comment->setUser($this->getUser());
        $comment->setPage($page->getPage());

        $form = $this->createForm(CommentType::class, $comment);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($comment);
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('success', $this->translator->trans('common.comment.submited'));

                return $this->redirectToRoute('darkwood_chat', ['ref' => $ref]);
            }
        }

        $query = $this->commentService->findActiveCommentByPageQuery($page->getPage());
        $comments = $this->paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('darkwood/pages/chat.html.twig', ['form' => $form, 'page' => $page, 'comments' => $comments]);
    }

    #[Route(path: ['fr' => '/liste-des-joueurs', 'en' => '/en/player-list', 'de' => '/de/liste-der-spieler'], name: 'users', defaults: ['ref' => 'users'])]
    public function users(Request $request, $ref): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->get('sort') && !in_array($request->get('sort'), ['u.created', 'u.username'], true)) {
            throw $this->createNotFoundException('Sort query is not allowed');
        }

        $page = $this->commonController->getPage($request, $ref);
        $query = $this->userService->findActiveQuery();
        $users = $this->paginator->paginate($query, $request->query->getInt('page', 1), 56);

        return $this->render('darkwood/pages/users.html.twig', ['page' => $page, 'users' => $users]);
    }

    #[Route(path: ['fr' => '/regles-du-jeu', 'en' => '/en/rules-of-the-game', 'de' => '/de/regeln-des-spiels'], name: 'rules', defaults: ['ref' => 'rules'])]
    public function rules(Request $request, $ref): \Symfony\Component\HttpFoundation\Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('darkwood/pages/rules.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/livre-d-or', 'en' => '/en/guestbook', 'de' => '/de/gastebuch'], name: 'guestbook', defaults: ['ref' => 'guestbook'])]
    public function guestbook(Request $request, $ref)
    {
        if ($request->get('sort') && $request->get('sort') !== 'c.created') {
            throw $this->createNotFoundException('Sort query is not allowed');
        }

        $page = $this->commonController->getPage($request, $ref);
        $comment = new CommentPage();
        $comment->setUser($this->getUser());
        $comment->setPage($page->getPage());

        $form = $this->createForm(CommentType::class, $comment);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($comment);
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('success', $this->translator->trans('common.comment.submited'));

                return $this->redirectToRoute('darkwood_guestbook', ['ref' => $ref]);
            }
        }

        $query = $this->commentService->findActiveCommentByPageQuery($page->getPage());
        $comments = $this->paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('darkwood/pages/guestbook.html.twig', ['form' => $form, 'page' => $page, 'comments' => $comments]);
    }

    #[Route(path: ['fr' => '/extra', 'en' => '/en/extra', 'de' => '/de/extra'], name: 'extra', defaults: ['ref' => 'extra'])]
    public function extra(Request $request, $ref): \Symfony\Component\HttpFoundation\Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('darkwood/pages/extra.html.twig', ['page' => $page, 'showLinks' => true]);
    }

    #[Route(path: ['fr' => '/classement', 'en' => '/en/rank', 'de' => '/de/rang'], name: 'rank', defaults: ['ref' => 'rank'])]
    public function rank(Request $request, $ref): \Symfony\Component\HttpFoundation\Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $mode = $request->get('mode');
        $query = $this->gameService->findActiveQuery($mode);
        $request->query->set('sort', preg_replace('/[^a-z.]/', '', $request->query->get('sort')));

        $players = $this->paginator->paginate($query, $request->query->getInt('page', 1), 56);

        return $this->render('darkwood/pages/rank.html.twig', ['page' => $page, 'players' => $players, 'mode' => $mode]);
    }
}
