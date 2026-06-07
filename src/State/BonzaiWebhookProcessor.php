<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class BonzaiWebhookProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $users,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new BadRequestHttpException('No current request.');
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            throw new BadRequestHttpException('Request body must be valid JSON.');
        }

        $email = trim((string) ($payload['email'] ?? ''));
        $orderId = trim((string) ($payload['order_id'] ?? ''));
        if ($email === '' || $orderId === '') {
            throw new BadRequestHttpException('email and order_id are required.');
        }

        $user = $this->users->findOneBy(['email' => $email]);
        if ($user === null) {
            $this->logger->warning('bonzai.webhook.user_not_found', ['email_hash' => hash('sha256', $email)]);

            return ['status' => 'ignored', 'reason' => 'user_not_found'];
        }

        $user->setBonzaiOrderId($orderId);
        $user->setIsPremium(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ['status' => 'ok', 'user_id' => $user->getId()];
    }
}
