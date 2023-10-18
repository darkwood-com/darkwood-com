<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserType;
use App\Services\UserService;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/users', name: 'admin_user_', host: '%admin_host%', priority : -1, requirements: ['_locale' => 'en|fr|de'])]
class UserController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly PaginatorInterface $paginator, private readonly UserService $userService)
    {
    }

    #[Route('/list', name: 'list')]
    public function list(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        $query = $this->userService->searchQuery($form->getData());
        $entities = $this->paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('admin/user/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        $entity = new User();
        $entity->setPassword('password');
        $entity->setCreated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->userService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('User not found');
        }

        $entity->setUpdated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        /** @var User $user */
        $user = $this->userService->findOneToEdit($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->userService->remove($user);
        // Launch the message flash
        $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));

        return $this->redirect($request->headers->get('referer'));
    }

    private function createSearchForm()
    {
        $data = [];

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_user_list'))->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_GET)->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('firstname', TextType::class, ['required' => false, 'label' => 'Prénom'])->add('lastname', TextType::class, ['required' => false, 'label' => 'Nom'])->add('email', TextType::class, ['required' => false, 'label' => 'Email'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
    }

    private function manage(Request $request, User $entity)
    {
        $mode = $entity->getId() ? 'edit' : 'create';
        $form = $this->createForm(UserType::class, $entity);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->save($entity);
                // Launch the message flash
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));

                return $this->redirectToRoute('admin_user_edit', ['id' => $entity->getId()]);
            }

            $this->container->get('request_stack')->getSession()->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }

        return $this->render('admin/user/' . $mode . '.html.twig', ['form' => $form, 'entity' => $entity]);
    }
}
