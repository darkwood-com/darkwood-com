<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Form\Admin\ArticleTranslationType;
use App\Services\ArticleService;
use App\Services\TagService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
#[\Symfony\Component\Routing\Annotation\Route('/{_locale}/articles', name: 'admin_article_', host: '%admin_host%', requirements: ['_locale' => 'en|fr|de'])]
class ArticleController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var PaginatorInterface
     */
    private $paginator;
    /**
     * @var ArticleService
     */
    private $articleService;
    /**
     * @var TagService
     */
    private $tagService;
    public function __construct(\Symfony\Contracts\Translation\TranslatorInterface $translator, \Knp\Component\Pager\PaginatorInterface $paginator, \App\Services\ArticleService $articleService, \App\Services\TagService $tagService)
    {
        $this->translator = $translator;
        $this->paginator = $paginator;
        $this->articleService = $articleService;
        $this->tagService = $tagService;
    }
    #[Route('/list', name: 'list')]
    public function list(\Symfony\Component\HttpFoundation\Request $request)
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);
        $query = $this->articleService->getQueryForSearch($form->getData(), $request->getLocale());
        $entities = $this->paginator->paginate($query, $request->query->get('page', 1), 20);
        return $this->render('admin/article/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }
    private function createSearchForm()
    {
        $data = [];
        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_article_list'))->setMethod('GET')->add('id', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Search'])->getForm();
    }
    private function manage(\Symfony\Component\HttpFoundation\Request $request, \App\Entity\ArticleTranslation $entityTranslation)
    {
        $mode = $entityTranslation->getId() ? 'edit' : 'create';
        $form = $this->createForm(\App\Form\Admin\ArticleTranslationType::class, $entityTranslation, ['locale' => $request->getLocale()]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->articleService->saveTranslation($entityTranslation, $form->get('export_locales')->getData() === true);
                // Launch the message flash
                $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));
                return $this->redirect($this->generateUrl('admin_article_edit', ['id' => $entityTranslation->getArticle()->getId()]));
            }
            $this->get('session')->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }
        return $this->render('admin/article/' . $mode . '.html.twig', ['form' => $form->createView(), 'entity' => $entityTranslation, 'tags' => $this->tagService->findAllAsArray($request->getLocale())]);
    }
    #[Route('/create', name: 'create')]
    public function create(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new \App\Entity\Article();
        $entity->setCreated(new \DateTime());
        $entityTranslation = new \App\Entity\ArticleTranslation();
        $entityTranslation->setLocale($request->getLocale());
        $entity->addTranslation($entityTranslation);
        return $this->manage($request, $entityTranslation);
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $entity = $this->articleService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Article not found');
        }
        $entity->setUpdated(new \DateTime());
        $entityTranslation = $entity->getOneTranslation($request->getLocale());
        if (!$entityTranslation instanceof \App\Entity\ArticleTranslation || $entityTranslation->getLocale() != $request->getLocale()) {
            $entityTranslation = new \App\Entity\ArticleTranslation();
            $entityTranslation->setLocale($request->getLocale());
            $entity->addTranslation($entityTranslation);
        }
        $entityTranslation->setUpdated(new \DateTime());
        return $this->manage($request, $entityTranslation);
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        /** @var Article $article */
        $article = $this->articleService->findOneToEdit($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }
        $this->articleService->remove($article);
        // Launch the message flash
        $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));
        return $this->redirect($request->headers->get('referer'));
    }
}
