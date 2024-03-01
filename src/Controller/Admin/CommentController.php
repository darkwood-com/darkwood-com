<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\CommentPage;
use App\Form\Admin\CommentType;
use App\Services\CommentService;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/comments', name: 'admin_comment_', host: '%admin_host%', priority : 10, requirements: ['_locale' => 'en|fr|de'])]
class CommentController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly PaginatorInterface $paginator, private readonly CommentService $commentService)
    {
    }

    #[Route('/list', name: 'list')]
    public function list(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        $query = $this->commentService->getQueryForSearch($form->getData());
        $entities = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 20);

        return $this->render('admin/comment/index.html.twig', ['entities' => $entities, 'search_form' => $form->createView()]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request)
    {
        $entity = new CommentPage();
        $entity->setCreated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, $id)
    {
        $entity = $this->commentService->findOneToEdit($id);
        if (!$entity) {
            throw $this->createNotFoundException('Comment not found');
        }

        $entity->setUpdated(new DateTime());

        return $this->manage($request, $entity);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        /** @var Comment $comment */
        $comment = $this->commentService->findOneToEdit($id);
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        $this->commentService->remove($comment);
        // Launch the message flash
        $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.deleted'));

        return $this->redirect($request->headers->get('referer'));
    }

    private function createSearchForm()
    {
        $data = [];

        return $this->createFormBuilder($data)->setAction($this->generateUrl('admin_comment_list'))->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_GET)->add('id', TextType::class, ['required' => false, 'label' => 'Id'])->add('submit', SubmitType::class, ['label' => 'Search'])->getForm();
    }

    private function manage(Request $request, Comment $entity)
    {
        $mode = $entity->getId() !== 0 ? 'edit' : 'create';
        $form = $this->createForm(CommentType::class, $entity, ['locale' => $request->getLocale()]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->commentService->save($entity);
                // Launch the message flash
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('notice', $this->translator->trans('notice.form.updated'));

                return $this->redirectToRoute('admin_comment_edit', ['id' => $entity->getId()]);
            }

            $this->container->get('request_stack')->getSession()->getFlashBag()->add('error', $this->translator->trans('notice.form.error'));
        }

        return $this->render('admin/comment/' . $mode . '.html.twig', ['form' => $form, 'entity' => $entity]);
    }
}
