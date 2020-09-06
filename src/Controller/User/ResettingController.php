<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\User;

use App\Controller\CommonController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @todo use : bin/console make:reset-password
 * Controller managing the resetting of the password.
 * @Route("/", name="common_resetting")
 */
class ResettingController extends AbstractController
{
    /**
     * @var CommonController
     */
    private $commonController;

    public function __construct(
        CommonController $commonController
    ) {
        $this->commonController = $commonController;
    }

    /**
     * Request reset user password: show form.
     *
     * @Route({ "fr": "/resetting/request", "en": "/en/resetting/request", "de": "/de/resetting/request" }, name="_request", defaults={"ref": "resetting"})
     */
    public function request(Request $request, $ref)
    {
        $page    = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        return $this->render('common/pages/resettingRequest.html.twig', [
            'page'     => $page,
            'site_ref' => $siteRef,
        ]);
    }

    /**
     * Request reset user password: submit form and send email.
     *
     * @Route({ "fr": "/resetting/send-email", "en": "/en/resetting/send-email", "de": "/de/resetting/send-email" }, name="_send_email", defaults={"ref": "resetting"})
     */
    public function sendEmail(Request $request, $ref)
    {
        $page    = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->render('common/pages/resettingRequest.html.twig', [
                'page'             => $page,
                'site_ref'         => $siteRef,
                'invalid_username' => $username,
            ]);
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->render('common/pages/resettingPasswordAlreadyRequested.html.twig', [
                'page'     => $page,
                'site_ref' => $siteRef,
            ]);
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->generateUrl('common_resetting_check_email',
            ['email' => $this->getObfuscatedEmail($user)]
        ));
    }

    /**
     * Tell the user to check his email provider.
     *
     * @Route({ "fr": "/resetting/check-email", "en": "/en/resetting/check-email", "de": "/de/resetting/check-email" }, name="_check_email", defaults={"ref": "resetting"})
     */
    public function checkEmail(Request $request, $ref)
    {
        $page    = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('common_resetting_request'));
        }

        return $this->render('common/pages/resettingCheckEmail.html.twig', [
            'page'     => $page,
            'site_ref' => $siteRef,
            'email'    => $email,
        ]);
    }

    /**
     * Reset user password.
     *
     * @Route({ "fr": "/resetting/reset/{token}", "en": "/en/resetting/reset/{token}", "de": "/de/resetting/reset/{token}" }, name="_reset", defaults={"ref": "resetting"})
     */
    public function reset(Request $request, $ref, $token)
    {
        $page    = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url      = $this->generateUrl('common_profile');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('common/pages/resettingReset.html.twig', [
            'page'     => $page,
            'site_ref' => $siteRef,
            'token'    => $token,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }
}
