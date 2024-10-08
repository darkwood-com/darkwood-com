<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Form\ProfileType;
use App\Services\GameService;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/', name: 'common_profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CommonController $commonController,
        private readonly UserService $userService,
        private readonly GameService $gameService
    ) {}

    #[Route(path: ['fr' => '/fr/profil/{username}', 'en' => '/profile/{username}', 'de' => '/de/profil/{username}'], name: '', defaults: ['ref' => 'profile'])]
    public function profile(Request $request, $ref, $username = null)
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        if ($username) {
            $user = $this->userService->findOneByUsername($username);
            if (!$user instanceof User) {
                throw $this->createNotFoundException('User not found !');
            }

            $playerInfo = null;
            if ($page->getPage()->getSite()->getRef() === 'darkwood' && $user->getPlayer()) {
                $playerInfo = $this->gameService->getInfo($user);
            }

            return $this->render('common/pages/profileShow.html.twig', ['page' => $page, 'user' => $user, 'playerInfo' => $playerInfo, 'site_ref' => $page->getPage()->getSite()->getRef()]);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found !');
        }

        $form = $this->createForm(ProfileType::class, $user, ['validation_groups' => ['Profile', 'Default']]);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->save($user);
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('success', $this->translator->trans('common.profile.submit_valid'));

                return $this->redirectToRoute('common_profile');
            }
        }

        return $this->render('common/pages/profile.html.twig', ['page' => $page, 'form' => $form, 'site_ref' => $siteRef]);
    }
}
