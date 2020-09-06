<?php

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Form\ProfileType;
use App\Services\GameService;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProfileController.
 *
 * @Route("/", name="common_profile")
 */
class ProfileController extends AbstractController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CommonController
     */
    private $commonController;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var GameService
     */
    private GameService $gameService;

    public function __construct(
        UserPasswordEncoderInterface $userPasswordEncoder,
        TranslatorInterface $translator,
        CommonController $commonController,
        UserService $userService,
        GameService $gameService
    ) {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->translator          = $translator;
        $this->commonController    = $commonController;
        $this->userService         = $userService;
        $this->gameService         = $gameService;
    }

    /**
     * @Route({ "fr": "/profil/{username}", "en": "/en/profile/{username}", "de": "/de/profil/{username}" }, name="", defaults={"ref": "profile"})
     */
    public function profile(Request $request, $ref, $username = null)
    {
        $page    = $this->commonController->getPage($request, $ref);
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

            return $this->render('common/pages/profileShow.html.twig', [
                'page'       => $page,
                'user'       => $user,
                'playerInfo' => $playerInfo,
                'site_ref'   => $page->getPage()->getSite()->getRef(),
            ]);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found !');
        }

        $form = $this->createForm(ProfileType::class, $user, [
            'validation_groups' => ['Profile', 'Default'],
        ]);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->get('plainPassword')->getData()) {
                if (!$this->userPasswordEncoder->isPasswordValid($user, $form)) {
                    $form->get('current_password');
                    $form->addError(new FormError('This value should be the user\'s current password.'));
                }
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->userService->save($user);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->translator->trans('common.profile.submit_valid')
                );

                return $this->redirect($this->generateUrl('common_profile'));
            }
        }

        return $this->render('common/pages/profile.html.twig', [
            'page'     => $page,
            'form'     => $form->createView(),
            'site_ref' => $siteRef,
        ]);
    }
}
