<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/', name: 'common_register')]
class RegistrationController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly EmailVerifier $emailVerifier, private readonly CommonController $commonController)
    {
    }

    #[Route(path: ['fr' => '/fr/inscription', 'en' => '/register', 'de' => '/de/registrieren'], name: '', defaults: ['ref' => 'register'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            try {
                $this->emailVerifier->sendEmailConfirmation('common_register_check', $user, (new TemplatedEmail())->from(new Address('no-reply@darkwood.com', 'Darkwood'))->to($user->getEmail())->subject('Please Confirm your Email')->htmlTemplate('common/mails/registration.html.twig')->context(['user' => $user]));
                $user->setEmailSent(true);
            } catch (\Symfony\Component\Mailer\Exception\TransportException) {
                $user->setEmailSent(false);
            }

            // do anything else you need here, like send an email
            return $userAuthenticator->authenticateUser($user, $authenticator, $request);
        }

        return $this->render('common/pages/register.html.twig', ['page' => $page, 'form' => $form, 'site_ref' => $siteRef]);
    }

    #[Route(path: ['fr' => '/fr/inscription/confimer-email', 'en' => '/register/check-email', 'de' => '/de/registrieren/check-email'], name: '_check', defaults: ['ref' => 'register'])]
    public function checkUserEmail(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $verifyEmailException) {
            $this->addFlash('verify_email_error', $verifyEmailException->getReason());

            return $this->redirectToRoute('common_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('common_register');
    }
}
