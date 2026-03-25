<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\SiteService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

#[Route('/2fa/setup', name: 'app_2fa_setup', methods: ['GET', 'POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TwoFactorController extends AbstractController
{
    private const SESSION_PENDING_SECRET_KEY = 'app.2fa.pending_secret';

    public function __construct(
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly UserService $userService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SiteService $siteService,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isTotpAuthenticationEnabled()) {
            return $this->render('security/two_factor_setup.html.twig', [
                'totpEnabled' => true,
                'secret' => null,
                'qrContent' => null,
                'backUrl' => $this->buildBackUrl($request),
            ]);
        }

        $session = $request->getSession();
        $pendingSecret = $session->get(self::SESSION_PENDING_SECRET_KEY);
        if (!\is_string($pendingSecret) || '' === $pendingSecret) {
            $pendingSecret = $this->totpAuthenticator->generateSecret();
            $session->set(self::SESSION_PENDING_SECRET_KEY, $pendingSecret);
        }

        $qrContent = $this->buildQrContent($user, $pendingSecret);

        if ($request->isMethod('POST')) {
            $code = trim((string) $request->request->get('auth_code'));

            if ('' === $code) {
                $this->addFlash('error', 'Enter the code from your authenticator app.');
            } elseif ($this->isValidSetupCode($user, $pendingSecret, $code)) {
                $user->setTotpSecret($pendingSecret);
                $this->userService->save($user);
                $session->remove(self::SESSION_PENDING_SECRET_KEY);
                $this->addFlash('success', 'Two-factor authentication is now enabled.');

                return $this->redirect($this->buildBackUrl($request));
            } else {
                $this->addFlash('error', 'The authentication code is invalid.');
            }
        }

        return $this->render('security/two_factor_setup.html.twig', [
            'totpEnabled' => false,
            'secret' => $pendingSecret,
            'qrContent' => $qrContent,
            'backUrl' => $this->buildBackUrl($request),
        ]);
    }

    private function buildQrContent(User $user, string $secret): string
    {
        $previousSecret = $user->getTotpSecret();
        $user->setTotpSecret($secret);

        try {
            return $this->totpAuthenticator->getQRContent($user);
        } finally {
            $user->setTotpSecret($previousSecret);
        }
    }

    private function isValidSetupCode(User $user, string $secret, string $code): bool
    {
        $previousSecret = $user->getTotpSecret();
        $user->setTotpSecret($secret);

        try {
            return $this->totpAuthenticator->checkCode($user, $code);
        } finally {
            $user->setTotpSecret($previousSecret);
        }
    }

    private function buildBackUrl(Request $request): string
    {
        $locale = $request->getLocale() ?: 'en';
        $host = $request->getHost();

        if ($host === $this->parameterBag->get('admin_host')) {
            return $this->urlGenerator->generate('admin_home', ['_locale' => $locale]);
        }

        $site = $this->siteService->findOneByHost($host);
        if (null !== $site) {
            return $this->urlGenerator->generate($site->getRef() . '_home', ['_locale' => $locale]);
        }

        return $this->urlGenerator->generate('darkwood_home', ['_locale' => $locale]);
    }
}
