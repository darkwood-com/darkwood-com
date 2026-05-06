<?php

declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;

final readonly class NewsletterSubscribersProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $items = [];
        foreach ($this->users->findBy(['newsletterEnabled' => true]) as $user) {
            $items[] = [
                'user_id' => (string) $user->getId(),
                'email' => (string) $user->getEmail(),
                'subscription_status' => $user->isPremium() ? 'premium' : 'free',
                'newsletter_enabled' => true,
                'custom_prompt' => $user->getCustomPrompt(),
            ];
        }

        return $items;
    }
}
