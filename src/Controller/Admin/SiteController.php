<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Form\Admin\SiteType;
use App\Services\SiteService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
/**
 * @Route("/{_locale}/sites", name="admin_site_", host="%admin_host%", requirements={"_locale":"en|fr|de"})
 */
class SiteController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
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
     * @var SiteService
     */
    private $siteService;
    public function __construct(\Symfony\Contracts\Translation\TranslatorInterface $translator, \Knp\Component\Pager\PaginatorInterface $paginator, \App\Services\SiteService $siteService)
    {
        $this->translator = $translator;
        $this->paginator = $paginator;
        $this->siteService = $siteService;
    }
    /**
     * @Route("/list", name="list")
     */
    public function list(\Symfony\Component\HttpFoundation\Request $request)
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);
        $query = $this->siteService->searchQuery($form->getData())->addOrderBy('s.position', 'asc');
        $entities = $this->paginator->paginate($query, $request->query->get('page', 1), 20);
        return $this->render('admin/site/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }
    private function createSearchForm()
    {
        $data = [];
        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_site_list'))->setMethod('GET')->add('id', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Search'])->getForm();
    }
    private function manage(\Symfony\Component\HttpFoundation\Request $request, \App\Entity\Site $entity)
    {
        $mode = $entity->getId() ? 'edit' : 'create';
        $form = $this->createForm(\App\Form\Admin\SiteType::class, $entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->siteService->save($entity);
                // Launch the message flash
                $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));
                return $this->redirect($this->generateUrl('admin_site_edit', ['id' => $entity->getId()]));
            }
            $this->get('session')->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }
        return $this->render('admin/site/' . $mode . '.html.twig', ['form' => $form->createView(), 'entity' => $entity]);
    }
    /**
     * @Route("/create", name="create")
     */
    public function create(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new \App\Entity\Site();
        $entity->setCreated(new \DateTime());
        return $this->manage($request, $entity);
    }
    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $entity = $this->siteService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Site not found');
        }
        $entity->setUpdated(new \DateTime());
        return $this->manage($request, $entity);
    }
    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        /** @var Site $site */
        $site = $this->siteService->findOneToEdit($id);
        if (!$site) {
            throw $this->createNotFoundException('Site not found');
        }
        $this->siteService->remove($site);
        // Launch the message flash
        $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));
        return $this->redirect($request->headers->get('referer'));
    }
    public function listNavBar($route, $params)
    {
        $sites = $this->siteService->findAll();
        return $this->render('admin/site/partials/navbar.html.twig', ['route' => $route, 'params' => $params, 'sites' => $sites]);
    }
}
