<?php

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * Class RegistrationController.
 *
 * @Route("/", name="common_register")
 */
class RegistrationController extends AbstractController
{
    /**
     * @var CommonController
     */
    private $commonController;

    private $emailVerifier;

    public function __construct(
        EmailVerifier $emailVerifier,
        CommonController $commonController
    ) {
        $this->emailVerifier    = $emailVerifier;
        $this->commonController = $commonController;
    }

    /**
     * @Route({ "fr": "/inscription", "en": "/en/register", "de": "/de/registrieren" }, name="", defaults={"ref": "register"})
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        $ref
    ): Response {
        $page    = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('common_register_check', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@darkwood.fr', 'Darkwood'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('common/mails/registration.html.twig')
                    ->context(['user' => $user])
            );
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('common/pages/register.html.twig', [
            'page'     => $page,
            'form'     => $form->createView(),
            'site_ref' => $siteRef,
        ]);
    }

    /**
     * @Route({ "fr": "/inscription/confimer-email", "en": "/en/register/check-email", "de": "/de/registrieren/check-email" }, name="_check", defaults={"ref": "register"})
     */
    public function checkUserEmail(Request $request, $ref): Response
    {
        $page    = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('common_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('common_register');
    }
}
