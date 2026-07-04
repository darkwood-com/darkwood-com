<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\CommonController;
use App\Entity\User;
use App\Form\ResettingType;
use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller managing the resetting of the password.
 */
#[Route]
class ResettingController extends AbstractController
{
    private const int RESET_TOKEN_TTL_HOURS = 1;

    public function __construct(
        private readonly CommonController $commonController,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
    ) {}

    /**
     * Request reset user password: show form.
     */
    #[Route(path: ['fr' => '/fr/resetting/request', 'en' => '/resetting/request', 'de' => '/de/resetting/request'], name: 'common_resetting_request', defaults: ['ref' => 'resetting'])]
    public function request(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        return $this->render('common/pages/resettingRequest.html.twig', ['page' => $page, 'site_ref' => $siteRef]);
    }

    /**
     * Request reset user password: submit form and send email.
     */
    #[Route(path: ['fr' => '/fr/resetting/send-email', 'en' => '/resetting/send-email', 'de' => '/de/resetting/send-email'], name: 'common_resetting_send_email', defaults: ['ref' => 'resetting'], methods: ['POST'])]
    public function sendEmail(Request $request, $ref): RedirectResponse
    {
        $username = (string) $request->request->get('username', '');
        $user = $this->userRepository->loadUserByIdentifier($username);

        if ($user instanceof User) {
            $token = bin2hex(random_bytes(32));
            $user->setResetToken($token);
            $user->setResetRequestedAt(new DateTimeImmutable());
            $this->entityManager->flush();

            $confirmationUrl = $this->generateUrl('common_resetting_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            try {
                $this->mailer->send(
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@darkwood.fr', 'Darkwood NoReply'))
                        ->to($user->getEmail())
                        ->subject('[Darkwood] Réinitialisation de votre mot de passe')
                        ->htmlTemplate('common/mails/resetting.html.twig')
                        ->context([
                            'user' => $user,
                            'confirmationUrl' => $confirmationUrl,
                        ])
                );
            } catch (TransportException) {
                // Do not leak whether the user exists; redirect anyway.
            }
        }

        return $this->redirectToRoute('common_resetting_check_email', ['email' => $username]);
    }

    /**
     * Tell the user to check their email.
     */
    #[Route(path: ['fr' => '/fr/resetting/check-email', 'en' => '/resetting/check-email', 'de' => '/de/resetting/check-email'], name: 'common_resetting_check_email', defaults: ['ref' => 'resetting'])]
    public function checkEmail(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        return $this->render('common/pages/resettingCheckEmail.html.twig', [
            'page' => $page,
            'site_ref' => $siteRef,
            'email' => $request->query->get('email', ''),
        ]);
    }

    /**
     * Reset user password: show form and handle submission.
     */
    #[Route(path: ['fr' => '/fr/resetting/reset/{token}', 'en' => '/resetting/reset/{token}', 'de' => '/de/resetting/reset/{token}'], name: 'common_resetting_reset', defaults: ['ref' => 'resetting'])]
    public function reset(Request $request, string $token, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        $user = $this->userRepository->findOneBy(['resetToken' => $token]);

        if (!$user instanceof User || !$this->isResetTokenValid($user)) {
            return $this->render('common/pages/resettingPasswordAlreadyRequested.html.twig', [
                'page' => $page,
                'site_ref' => $siteRef,
            ]);
        }

        $form = $this->createForm(ResettingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setResetToken(null);
            $user->setResetRequestedAt(null);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your password has been reset.');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('common/pages/resettingReset.html.twig', [
            'page' => $page,
            'site_ref' => $siteRef,
            'token' => $token,
            'form' => $form,
        ]);
    }

    private function isResetTokenValid(User $user): bool
    {
        $requestedAt = $user->getResetRequestedAt();

        if (null === $requestedAt) {
            return false;
        }

        $expiresAt = DateTimeImmutable::createFromInterface($requestedAt)->add(new DateInterval('PT' . self::RESET_TOKEN_TTL_HOURS . 'H'));

        return $expiresAt > new DateTimeImmutable();
    }
}
