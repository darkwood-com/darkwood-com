<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\CommentArticle;
use App\Entity\User;
use App\Enum\ArticleReactionEmoji;
use App\Form\CommentType;
use App\Service\ArticleReactionService;
use App\Service\BlogArticleService;
use App\Service\CommentService;
use App\Service\DarkwoodEntitlementService;
use App\Validator\Constraints\PaginationDTO;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(host: '%blog_host%')]
class BlogController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
        private readonly TranslatorInterface $translator,
        private readonly PaginatorInterface $paginator,
        private readonly BlogArticleService $articleService,
        private readonly CommentService $commentService,
        private readonly DarkwoodEntitlementService $entitlementService,
        private readonly ArticleReactionService $articleReactionService,
    ) {}

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'blog_home', defaults: ['ref' => 'home'])]
    public function home(Request $request, #[MapQueryString] ?PaginationDTO $pagination, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $query = $this->articleService->findActivesQueryBuilder($request->getLocale());
        $request->query->set('sort', $pagination?->sort ?? '');

        $articles = $this->paginator->paginate($query, $pagination?->page ?? 1, 10);
        $lastWatchArticle = $this->articleService->findLatestWatchArticle($request->getLocale());
        $lastReleaseArticle = $this->articleService->findLatestReleaseArticle($request->getLocale());
        $lastCreatorArticle = $this->articleService->findLatestCreatorArticle($request->getLocale());
        $reactionArticles = array_values(array_filter([$lastWatchArticle, $lastReleaseArticle, $lastCreatorArticle]));
        foreach ($articles as $article) {
            $reactionArticles[] = $article;
        }

        return $this->render('blog/pages/home.html.twig', [
            'page' => $page,
            'articles' => $articles,
            'lastWatchArticle' => $lastWatchArticle,
            'lastReleaseArticle' => $lastReleaseArticle,
            'lastCreatorArticle' => $lastCreatorArticle,
            'reactionSummaries' => $this->buildReactionSummaries($reactionArticles),
            'reactionEmojis' => $this->articleReactionService->getAvailableEmojis(),
            'showLinks' => true,
        ]);
    }

    #[Route(path: ['fr' => '/fr/watch', 'en' => '/watch', 'de' => '/de/watch'], name: 'blog_watch', defaults: ['ref' => 'watch'])]
    public function watch(Request $request, #[MapQueryString] ?PaginationDTO $pagination, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $query = $this->articleService->findWatchActivesQueryBuilder($request->getLocale());
        $articles = $this->paginator->paginate($query, $pagination?->page ?? 1, 10);

        return $this->render('blog/pages/home.html.twig', [
            'page' => $page,
            'articles' => $articles,
            'lastWatchArticle' => null,
            'lastReleaseArticle' => null,
            'lastCreatorArticle' => null,
            'reactionSummaries' => $this->buildReactionSummaries(iterator_to_array($articles)),
            'reactionEmojis' => $this->articleReactionService->getAvailableEmojis(),
            'showLinks' => true,
        ]);
    }

    #[Route(path: ['fr' => '/fr/release', 'en' => '/release', 'de' => '/de/release'], name: 'blog_release', defaults: ['ref' => 'release'])]
    public function release(Request $request, #[MapQueryString] ?PaginationDTO $pagination, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $query = $this->articleService->findReleaseActivesQueryBuilder($request->getLocale());
        $articles = $this->paginator->paginate($query, $pagination?->page ?? 1, 10);

        return $this->render('blog/pages/home.html.twig', [
            'page' => $page,
            'articles' => $articles,
            'lastWatchArticle' => null,
            'lastReleaseArticle' => null,
            'lastCreatorArticle' => null,
            'reactionSummaries' => $this->buildReactionSummaries(iterator_to_array($articles)),
            'reactionEmojis' => $this->articleReactionService->getAvailableEmojis(),
            'showLinks' => true,
        ]);
    }

    #[Route(path: ['fr' => '/fr/creator', 'en' => '/creator', 'de' => '/de/creator'], name: 'blog_creator', defaults: ['ref' => 'creator'])]
    public function creator(Request $request, #[MapQueryString] ?PaginationDTO $pagination, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $query = $this->articleService->findCreatorActivesQueryBuilder($request->getLocale());
        $articles = $this->paginator->paginate($query, $pagination?->page ?? 1, 10);

        return $this->render('blog/pages/home.html.twig', [
            'page' => $page,
            'articles' => $articles,
            'lastWatchArticle' => null,
            'lastReleaseArticle' => null,
            'lastCreatorArticle' => null,
            'reactionSummaries' => $this->buildReactionSummaries(iterator_to_array($articles)),
            'reactionEmojis' => $this->articleReactionService->getAvailableEmojis(),
            'showLinks' => true,
        ]);
    }

    #[Route(path: ['fr' => '/fr/mentions-legales', 'en' => '/legal-mentions', 'de' => '/de/impressum'], name: 'blog_legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref): Response
    {
        $response = $this->commonController->legalMention($request, $ref);
        $sessionCookieName = $request->hasSession() ? $request->getSession()->getName() : session_name();
        $hasPrivateContext = $request->headers->has('Authorization')
            || $request->cookies->has($sessionCookieName)
            || $request->cookies->has('REMEMBERME');

        if ($hasPrivateContext) {
            $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');

            return $response;
        }

        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, '1');

        return $response
            ->setPublic()
            ->setMaxAge(0)
            ->setSharedMaxAge(300)
        ;
    }

    #[Route(path: ['fr' => '/fr/plan-du-site', 'en' => '/sitemap', 'de' => '/de/sitemap'], name: 'blog_sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'blog_sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route(path: ['fr' => '/fr/rss', 'en' => '/rss', 'de' => '/de/rss'], name: 'blog_rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    #[Route(path: ['fr' => '/fr/contact', 'en' => '/contact', 'de' => '/de/kontakt'], name: 'blog_contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/article/{slug}', 'en' => '/article/{slug}', 'de' => '/de/article/{slug}'], name: 'blog_article', defaults: ['ref' => 'article', 'slug' => null])]
    public function article(Request $request, $ref, $slug)
    {
        if ($request->query->get('sort') && $request->query->get('sort') !== 'a.created') {
            throw $this->createNotFoundException('Sort query is not allowed');
        }

        $page = $this->commonController->getPage($request, $ref);
        $article = $this->articleService->findOneBySlug($slug, $request->getLocale());
        if (!$article instanceof Article) {
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

        return $this->render('blog/pages/article.html.twig', [
            'page' => $page,
            'article' => $article,
            'entity' => $article->getOneTranslation($request->getLocale()),
            'showLinks' => true,
            'form' => $form,
            'comments' => $comments,
            'isPremiumUser' => $isPremiumUser,
            'reactionSummary' => $this->articleReactionService->getSummary($article, $this->getUser()),
            'reactionEmojis' => $this->articleReactionService->getAvailableEmojis(),
            'relatedArticles' => $this->articleService->findRelatedArticles($article, $request->getLocale(), 4),
        ]);
    }

    #[Route(path: ['fr' => '/fr/article/{slug}/reactions', 'en' => '/article/{slug}/reactions', 'de' => '/de/article/{slug}/reactions'], name: 'blog_article_reaction', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleArticleReaction(Request $request, string $slug): JsonResponse
    {
        if (!$this->isCsrfTokenValid('article_reaction', (string) $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_BAD_REQUEST);
        }

        $emoji = ArticleReactionEmoji::tryFromRequest($request->request->getString('emoji'));
        if (!$emoji instanceof ArticleReactionEmoji) {
            return new JsonResponse(['error' => 'Invalid emoji.'], Response::HTTP_BAD_REQUEST);
        }

        $article = $this->articleService->findOneBySlug($slug, $request->getLocale());
        if (!$article instanceof Article) {
            throw $this->createNotFoundException('Article not found !');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $summary = $this->articleReactionService->toggleReaction($article, $emoji, $user);

        return new JsonResponse($summary);
    }

    /**
     * @param list<Article> $articles
     *
     * @return array<int, array{counts: array<string, int>, userReactions: list<string>}>
     */
    private function buildReactionSummaries(array $articles): array
    {
        return $this->articleReactionService->getSummariesForArticles($articles, $this->getUser());
    }
}
