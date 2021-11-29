<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\Admin\ContactType;
use App\Services\ContactService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/contacts', name: 'admin_contact_', host: '%admin_host%', priority : -1, requirements: ['_locale' => 'en|fr|de'])]
class ContactController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private TranslatorInterface $translator, private PaginatorInterface $paginator, private ContactService $contactService)
    {
    }

    #[Route('/list', name: 'list')]
    public function list(Request $request)
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);
        $query    = $this->contactService->getQueryForSearch($form->getData());
        $entities = $this->paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('admin/contact/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    private function createSearchForm()
    {
        $data = [];

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_contact_list'))->setMethod('GET')->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
    }

    private function manage(Request $request, Contact $entity)
    {
        $mode = $entity->getId() ? 'edit' : 'create';
        $form = $this->createForm(ContactType::class, $entity, ['locale' => $request->getLocale()]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->contactService->save($entity);
                // Launch the message flash
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));

                return $this->redirect($this->generateUrl('admin_contact_edit', ['id' => $entity->getId()]));
            }
            $this->container->get('request_stack')->getSession()->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }

        return $this->render('admin/contact/' . $mode . '.html.twig', ['form' => $form->createView(), 'entity' => $entity]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        $entity = new Contact();
        $entity->setCreated(new \DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->contactService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Contact not found');
        }
        $entity->setUpdated(new \DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, $id)
    {
        /** @var Contact $contact */
        $contact = $this->contactService->findOneToEdit($id);
        if (!$contact) {
            throw $this->createNotFoundException('Contact not found');
        }
        $this->contactService->remove($contact);
        // Launch the message flash
        $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));

        return $this->redirect($request->headers->get('referer'));
    }
}
