<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Form\Admin\SiteType;
use App\Services\SiteService;
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

#[Route('/{_locale}/sites', name: 'admin_site_', host: '%admin_host%', priority : 10, requirements: ['_locale' => 'en|fr|de'])]
class SiteController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly PaginatorInterface $paginator, private readonly SiteService $siteService) {}

    #[Route('/list', name: 'list')]
    public function list(Request $request): Response
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        $query = $this->siteService->searchQuery($form->getData())->addOrderBy('s.position', 'asc');
        $entities = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 20);

        return $this->render('admin/site/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        $entity = new Site();
        $entity->setCreated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->siteService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Site not found');
        }

        $entity->setUpdated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, $id): RedirectResponse
    {
        /** @var Site $site */
        $site = $this->siteService->findOneToEdit($id);
        if (!$site) {
            throw $this->createNotFoundException('Site not found');
        }

        $this->siteService->remove($site);
        // Launch the message flash
        $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));

        return $this->redirect($request->headers->get('referer'));
    }

    public function listNavBar($route, $params)
    {
        $sites = $this->siteService->findAll();

        return $this->render('admin/site/partials/navbar.html.twig', ['route' => $route, 'params' => $params, 'sites' => $sites]);
    }

    private function createSearchForm()
    {
        $data = [];

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_site_list'))->setMethod(Request::METHOD_GET)->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
    }

    private function manage(Request $request, Site $entity)
    {
        $mode = $entity->getId() !== 0 ? 'edit' : 'create';
        $form = $this->createForm(SiteType::class, $entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->siteService->save($entity);
                // Launch the message flash
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));

                return $this->redirectToRoute('admin_site_edit', ['id' => $entity->getId()]);
            }

            $this->container->get('request_stack')->getSession()->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }

        return $this->render('admin/site/' . $mode . '.html.twig', ['form' => $form, 'entity' => $entity]);
    }
}
