<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Newsletter\NewsletterUnsubscribeTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Mathieu
 */
#[Route(host: '%api_host%')]
final class NewsletterUnsubscribeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly NewsletterUnsubscribeTokenService $tokens,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/newsletter/unsubscribe/{userId}/{token}', name: 'newsletter_unsubscribe', methods: ['GET'])]
    public function unsubscribe(int $userId, string $token): Response
    {
        $user = $this->users->find($userId);
        if (null === $user) {
            return $this->render('newsletter/unsubscribe.html.twig', [
                'success' => false,
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        $email = (string) $user->getEmail();
        if (!$this->tokens->isValid($userId, $email, $token)) {
            return $this->render('newsletter/unsubscribe.html.twig', [
                'success' => false,
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        if ($user->isNewsletterEnabled()) {
            $user->setNewsletterEnabled(false);
            $this->entityManager->flush();
        }

        return $this->render('newsletter/unsubscribe.html.twig', [
            'success' => true,
        ]);
    }
}
