<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\App;
use App\Entity\AppContent;
use App\Entity\CommentPage;
use App\Form\CommentType;
use App\Service\CommentService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(host: '%apps_host%')]
class AppsController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
        private readonly TranslatorInterface $translator,
        private readonly PaginatorInterface $paginator,
        private readonly CommentService $commentService,
    ) {}

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'apps_home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('apps/pages/home.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/fr/mentions-legales', 'en' => '/legal-mentions', 'de' => '/de/impressum'], name: 'apps_legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref)
    {
        return $this->commonController->legalMention($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/plan-du-site', 'en' => '/sitemap', 'de' => '/de/sitemap'], name: 'apps_sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'apps_sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route(path: ['fr' => '/fr/rss', 'en' => '/rss', 'de' => '/de/rss'], name: 'apps_rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    #[Route(path: ['fr' => '/fr/contact', 'en' => '/contact', 'de' => '/de/kontakt'], name: 'apps_contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/{ref}/{slug}', 'de' => '/de/{ref}/{slug}', 'en' => '/{ref}/{slug}'], name: 'apps_app', defaults: ['ref' => null, 'slug' => null], priority: -10)]
    public function app(Request $request, $ref, $slug = null)
    {
        if ($request->query->get('sort') && $request->query->get('sort') !== 'c.created') {
            throw $this->createNotFoundException('Sort query is not allowed');
        }

        $page = $this->commonController->getPage($request, $ref);
        $app = $page->getPage();
        if (!$app instanceof App) {
            throw $this->createNotFoundException('App not found !');
        }

        $contents = $app->getContents()->filter(static fn ($appContent)
            // @var AppContent $appContent
            => $appContent->getLocale() === $request->getLocale());
        $content = $page->getContent();
        if (null !== $slug) {
            $content = $contents->filter(static fn ($appContent)
                // @var AppContent $appContent
                => $appContent->getSlug() === $slug)->current();
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
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('success', $this->translator->trans('common.comment.submited'));

                return $this->redirectToRoute('apps_app', ['ref' => $ref, 'slug' => $slug]);
            }
        }

        $query = $this->commentService->findActiveCommentByPageQuery($page->getPage());
        $comments = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 10);

        return $this->render('apps/pages/app.html.twig', ['page' => $page, 'slug' => $slug, 'contents' => $contents, 'content' => $content, 'showLinks' => true, 'form' => $form, 'comments' => $comments]);
    }
}
