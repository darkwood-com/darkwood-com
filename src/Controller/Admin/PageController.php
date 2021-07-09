<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Form\Admin\PageTranslationType;
use App\Services\PageService;
use App\Services\SiteService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/pages', name: 'admin_page_', host: '%admin_host%', priority : -1, requirements: ['_locale' => 'en|fr|de'])]
class PageController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private TranslatorInterface $translator, private PaginatorInterface $paginator, private PageService $pageService, private SiteService $siteService)
    {
    }

    #[Route('/list', name: 'list')]
    public function list(Request $request)
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);
        $query    = $this->pageService->getQueryForSearch($form->getData(), 'page', null, $request->getLocale());
        $entities = $this->paginator->paginate($query, $request->query->get('page', 1), 20);

        return $this->render('admin/page/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    private function createSearchForm()
    {
        $data = [];

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_page_list'))->setMethod('GET')->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
    }

    private function manage(Request $request, PageTranslation $entityTranslation)
    {
        $mode = $entityTranslation->getId() ? 'edit' : 'create';
        $form = $this->createForm(PageTranslationType::class, $entityTranslation, ['locale' => $request->getLocale()]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->pageService->saveTranslation($entityTranslation, $form->get('export_locales')->getData() === true);
                // Launch the message flash
                $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));

                return $this->redirect($this->generateUrl('admin_page_edit', ['id' => $entityTranslation->getPage()->getId()]));
            }
            $this->get('session')->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }

        return $this->render('admin/page/' . $mode . '.html.twig', ['form' => $form->createView(), 'entity' => $entityTranslation, 'url' => $entityTranslation->getId() ? $this->pageService->getUrl($entityTranslation, true, true) : null]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        $entity = new Page();
        $entity->setCreated(new \DateTime());
        $entityTranslation = new PageTranslation();
        $entityTranslation->setLocale($request->getLocale());
        $entity->addTranslation($entityTranslation);

        return $this->manage($request, $entityTranslation);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->pageService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Page not found');
        }
        $entity->setUpdated(new \DateTime());
        $entityTranslation = $entity->getOneTranslation($request->getLocale());
        if (!$entityTranslation instanceof PageTranslation || $entityTranslation->getLocale() != $request->getLocale()) {
            $entityTranslation = new PageTranslation();
            $entityTranslation->setLocale($request->getLocale());
            $entity->addTranslation($entityTranslation);
        }
        $entityTranslation->setUpdated(new \DateTime());

        return $this->manage($request, $entityTranslation);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, $id)
    {
        /** @var Page $page */
        $page = $this->pageService->findOneToEdit($id);
        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }
        $this->pageService->remove($page);
        // Launch the message flash
        $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));

        return $this->redirect($request->headers->get('referer'));
    }
}
