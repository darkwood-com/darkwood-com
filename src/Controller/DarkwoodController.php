<?php

namespace App\Controller;

use App\Entity\CommentPage;
use App\Form\CommentType;
use App\Services\ArticleService;
use App\Services\CommentService;
use App\Services\GameService;
use App\Services\PageService;
use App\Services\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/", name="darkwood_", host="%darkwood_host%")
 */
class DarkwoodController extends AbstractController
{
    /**
     * @var CommonController
     */
    private $commonController;

    /**
     * @var ArticleService
     */
    private $articleService;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var PageService
     */
    private $pageService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var GameService
     */
    private $gameService;

    public function __construct(
        CommonController $commonController,
        ArticleService $articleService,
        AuthenticationUtils $authenticationUtils,
        TranslatorInterface $translator,
        PaginatorInterface $paginator,
        PageService $pageService,
        CommentService $commentService,
        UserService $userService,
        GameService $gameService
    ) {
        $this->commonController    = $commonController;
        $this->articleService      = $articleService;
        $this->authenticationUtils = $authenticationUtils;
        $this->translator          = $translator;
        $this->paginator           = $paginator;
        $this->pageService         = $pageService;
        $this->commentService      = $commentService;
        $this->userService         = $userService;
        $this->gameService         = $gameService;
    }

    public function menu(Request $request, $ref, $entity)
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken    = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());

        return $this->render('darkwood/partials/menu.html.twig', [
            'last_username' => $lastUsername,
            'csrf_token'    => $csrfToken,
            'pageLinks'     => $pageLinks,
        ]);
    }

    /**
     * @Route({ "fr": "/", "en": "/en", "de": "/de" }, name="home", defaults={"ref": "home"})
     */
    public function home(Request $request, $ref)
    {
        $page     = $this->commonController->getPage($request, $ref);
        $articles = $this->articleService->findActives($request->getLocale(), 5);

        return $this->render('darkwood/pages/home.html.twig', [
            'page'      => $page,
            'news'      => $articles,
            'showLinks' => true,
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

    /**
     * @Route({ "fr": "/news/{slug}", "en": "/en/news/{slug}", "de": "/de/news/{slug}" }, name="news", defaults={"ref": "news", "slug": null})
     */
    public function news(Request $request, $ref, $slug)
    {
        $page = $this->commonController->getPage($request, $ref);

        $news = $this->articleService->findOneBySlug($slug, $request->getLocale());
        if (!$news) {
            throw $this->createNotFoundException('News not found !');
        }

        return $this->render('darkwood/pages/news.html.twig', [
            'page'      => $page,
            'news'      => $news,
            'showLinks' => true,
        ]);
    }

    /**
     * @Route({ "fr": "/jouer/{display}", "en": "/en/play/{display}", "de": "/de/spiel/{display}" }, name="play", defaults={"ref": "play", "display": null})
     */
    public function play(Request $request, $ref = 'play', $display = null)
    {
        $page       = $this->commonController->getPage($request, $ref);
        $parameters = $this->gameService->play($request, $this->getUser(), $display);

        if ($parameters instanceof Response) {
            return $parameters;
        }

        $parameters['page'] = $page;

        if ($request->isXmlHttpRequest()) {
            return $this->render('darkwood/partials/play/' . $parameters['display'] . '/' . $parameters['state'] . '.html.twig', $parameters);
        }

        return $this->render('darkwood/pages/play.html.twig', $parameters);
    }

    /**
     * @Route({ "fr": "/chat", "en": "/en/chat", "de": "/de/chat" }, name="chat", defaults={"ref": "chat"})
     */
    public function chat(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        $comment = new CommentPage();
        $comment->setUser($this->getUser());
        $comment->setPage($page->getPage());

        $form = $this->createForm(CommentType::class, $comment);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($comment);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->translator->trans('common.comment.submited')
                );

                return $this->redirect($this->generateUrl('darkwood_chat', ['ref' => $ref]));
            }
        }

        $query = $this->commentService->findActiveCommentByPageQuery($page->getPage());

        $comments = $this->paginator->paginate(
            $query,
            $request->query->get('page', 1),
            10
        );

        return $this->render('darkwood/pages/chat.html.twig', [
            'form'     => $form->createView(),
            'page'     => $page,
            'comments' => $comments,
        ]);
    }

    /**
     * @Route({ "fr": "/liste-des-joueurs", "en": "/en/player-list", "de": "/de/liste-der-spieler" }, name="users", defaults={"ref": "users"})
     */
    public function users(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        $query = $this->userService->findActiveQuery();

        $users = $this->paginator->paginate(
            $query,
            $request->query->get('page', 1),
            56
        );

        return $this->render('darkwood/pages/users.html.twig', [
            'page'  => $page,
            'users' => $users,
        ]);
    }

    /**
     * @Route({ "fr": "/regles-du-jeu", "en": "/en/rules-of-the-game", "de": "/de/regeln-des-spiels" }, name="rules", defaults={"ref": "rules"})
     */
    public function rules(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('darkwood/pages/rules.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route({ "fr": "/livre-d-or", "en": "/en/guestbook", "de": "/de/gastebuch" }, name="guestbook", defaults={"ref": "guestbook"})
     */
    public function guestbook(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        $comment = new CommentPage();
        $comment->setUser($this->getUser());
        $comment->setPage($page->getPage());

        $form = $this->createForm(CommentType::class, $comment);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($comment);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->translator->trans('common.comment.submited')
                );

                return $this->redirect($this->generateUrl('darkwood_guestbook', ['ref' => $ref]));
            }
        }

        $query = $this->commentService->findActiveCommentByPageQuery($page->getPage());

        $comments = $this->paginator->paginate(
            $query,
            $request->query->get('page', 1),
            10
        );

        return $this->render('darkwood/pages/guestbook.html.twig', [
            'form'     => $form->createView(),
            'page'     => $page,
            'comments' => $comments,
        ]);
    }

    /**
     * @Route({ "fr": "/extra", "en": "/en/extra", "de": "/de/extra" }, name="extra", defaults={"ref": "extra"})
     */
    public function extra(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('darkwood/pages/extra.html.twig', [
            'page'      => $page,
            'showLinks' => true,
        ]);
    }

    /**
     * @Route({ "fr": "/classement", "en": "/en/rank", "de": "/de/rang" }, name="rank", defaults={"ref": "rank"})
     */
    public function rank(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
        $mode = $request->get('mode');

        $query = $this->gameService->findActiveQuery($mode);

        $players = $this->paginator->paginate(
            $query,
            $request->query->get('page', 1),
            56
        );

        return $this->render('darkwood/pages/rank.html.twig', [
            'page'    => $page,
            'players' => $players,
            'mode'    => $mode,
        ]);
    }
}
