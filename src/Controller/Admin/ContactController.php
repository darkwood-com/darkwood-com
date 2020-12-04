<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\Admin\ContactType;
use App\Services\ContactService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
#[\Symfony\Component\Routing\Annotation\Route('/{_locale}/contacts', name: 'admin_contact_', host: '%admin_host%', requirements: ['_locale' => 'en|fr|de'])]
class ContactController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
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
     * @var ContactService
     */
    private $contactService;
    public function __construct(\Symfony\Contracts\Translation\TranslatorInterface $translator, \Knp\Component\Pager\PaginatorInterface $paginator, \App\Services\ContactService $contactService)
    {
        $this->translator = $translator;
        $this->paginator = $paginator;
        $this->contactService = $contactService;
    }
    #[Route('/list', name: 'list')]
    public function list(\Symfony\Component\HttpFoundation\Request $request)
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);
        $query = $this->contactService->getQueryForSearch($form->getData());
        $entities = $this->paginator->paginate($query, $request->query->get('page', 1), 20);
        return $this->render('admin/contact/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }
    private function createSearchForm()
    {
        $data = [];
        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_contact_list'))->setMethod('GET')->add('id', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Search'])->getForm();
    }
    private function manage(\Symfony\Component\HttpFoundation\Request $request, \App\Entity\Contact $entity)
    {
        $mode = $entity->getId() ? 'edit' : 'create';
        $form = $this->createForm(\App\Form\Admin\ContactType::class, $entity, ['locale' => $request->getLocale()]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->contactService->save($entity);
                // Launch the message flash
                $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));
                return $this->redirect($this->generateUrl('admin_contact_edit', ['id' => $entity->getId()]));
            }
            $this->get('session')->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }
        return $this->render('admin/contact/' . $mode . '.html.twig', ['form' => $form->createView(), 'entity' => $entity]);
    }
    #[Route('/create', name: 'create')]
    public function create(\Symfony\Component\HttpFoundation\Request $request)
    {
        $entity = new \App\Entity\Contact();
        $entity->setCreated(new \DateTime());
        return $this->manage($request, $entity);
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $entity = $this->contactService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Contact not found');
        }
        $entity->setUpdated(new \DateTime());
        return $this->manage($request, $entity);
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        /** @var Contact $contact */
        $contact = $this->contactService->findOneToEdit($id);
        if (!$contact) {
            throw $this->createNotFoundException('Contact not found');
        }
        $this->contactService->remove($contact);
        // Launch the message flash
        $this->get('session')->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));
        return $this->redirect($request->headers->get('referer'));
    }
}
