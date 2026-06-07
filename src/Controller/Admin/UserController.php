<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserType;
use App\Service\UserService;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(name: 'admin_user_', requirements: ['_locale' => 'en|fr|de'], host: '%admin_host%', priority : 10)]
class UserController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly PaginatorInterface $paginator, private readonly UserService $userService) {}

    #[Route('/{_locale}/users/list', name: 'admin_user_list')]
    public function list(Request $request): Response
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        $query = $this->userService->searchQuery($form->getData());
        $entities = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 20);

        return $this->render('admin/user/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    #[Route('/{_locale}/users/create', name: 'admin_user_create')]
    public function create(Request $request)
    {
        $entity = new User();
        $entity->setPassword('password');
        $entity->setCreated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/{_locale}/users/edit/{id}', name: 'admin_user_edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->userService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('User not found');
        }

        $entity->setUpdated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/{_locale}/users/delete/{id}', name: 'admin_user_delete')]
    public function delete(Request $request, $id): RedirectResponse
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

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_user_list'))->setMethod(Request::METHOD_GET)->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('firstname', TextType::class, ['required' => false, 'label' => 'Prénom'])->add('lastname', TextType::class, ['required' => false, 'label' => 'Nom'])->add('email', TextType::class, ['required' => false, 'label' => 'Email'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
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
