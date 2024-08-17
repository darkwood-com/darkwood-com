<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Form\Admin\ArticleTranslationType;
use App\Services\ArticleService;
use App\Services\TagService;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/articles', name: 'admin_article_', host: '%admin_host%', priority : 10, requirements: ['_locale' => 'en|fr|de'])]
class ArticleController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly PaginatorInterface $paginator, private readonly ArticleService $articleService, private readonly TagService $tagService) {}

    #[Route('/list', name: 'list')]
    public function list(Request $request): Response
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        $query = $this->articleService->getQueryForSearch($form->getData(), $request->getLocale());
        $entities = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 20);

        return $this->render('admin/article/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        $entity = new Article();
        $entity->setCreated(new DateTime());

        $entityTranslation = new ArticleTranslation();
        $entityTranslation->setLocale($request->getLocale());

        $entity->addTranslation($entityTranslation);

        return $this->manage($request, $entityTranslation);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->articleService->findOneToEdit($id);
        if ($entity === null) {
            throw $this->createNotFoundException('Article not found');
        }

        $entity->setUpdated(new DateTime());
        $entityTranslation = $entity->getOneTranslation($request->getLocale());
        if (!$entityTranslation instanceof ArticleTranslation || $entityTranslation->getLocale() !== $request->getLocale()) {
            $entityTranslation = new ArticleTranslation();
            $entityTranslation->setLocale($request->getLocale());
            $entity->addTranslation($entityTranslation);
        }

        $entityTranslation->setUpdated(new DateTime());

        return $this->manage($request, $entityTranslation);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, $id): RedirectResponse
    {
        /** @var Article $article */
        $article = $this->articleService->findOneToEdit($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $this->articleService->remove($article);
        // Launch the message flash
        $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));

        return $this->redirect($request->headers->get('referer'));
    }

    private function createSearchForm()
    {
        $data = [];

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_article_list'))->setMethod(Request::METHOD_GET)->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
    }

    private function manage(Request $request, ArticleTranslation $entityTranslation)
    {
        $mode = $entityTranslation->getId() !== 0 ? 'edit' : 'create';
        $form = $this->createForm(ArticleTranslationType::class, $entityTranslation, ['locale' => $request->getLocale()]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->articleService->saveTranslation($entityTranslation, $form->get('export_locales')->getData() === true);
                // Launch the message flash
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));

                return $this->redirectToRoute('admin_article_edit', ['id' => $entityTranslation->getArticle()->getId()]);
            }

            $this->container->get('request_stack')->getSession()->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }

        return $this->render('admin/article/' . $mode . '.html.twig', ['form' => $form, 'entity' => $entityTranslation, 'tags' => $this->tagService->findAllAsArray($request->getLocale())]);
    }
}
