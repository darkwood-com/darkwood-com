<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\UserClaimsResolveEvent;
use App\Repository\UserRepository;
use App\Security\Oidc\RolesClaimSetConfigurator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Resolves OIDC ID-token claims from the Darkwood User identified by UUID subject.
 *
 * @author Mathieu Ledru
 */
final readonly class OidcUserClaimsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $users,
        /** Ensures the custom "roles" claim set is registered on the ClaimExtractor. */
        private RolesClaimSetConfigurator $rolesClaimSetConfigurator,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            UserClaimsResolveEvent::class => 'onUserClaimsResolve',
        ];
    }

    public function onUserClaimsResolve(UserClaimsResolveEvent $event): void
    {
        $identifier = $event->getIdentifier();
        if (!Uuid::isValid($identifier)) {
            return;
        }

        $user = $this->users->findOneByUuid(Uuid::fromString($identifier));
        if (null === $user) {
            return;
        }

        $event->setClaims([
            'email' => (string) $user->getEmail(),
            'email_verified' => (bool) $user->isVerified(),
            'preferred_username' => $user->getUsername(),
            'given_name' => $user->getFirstname(),
            'family_name' => $user->getLastname(),
            'name' => trim(sprintf('%s %s', (string) $user->getFirstname(), (string) $user->getLastname())),
            'roles' => array_values($user->getRoles()),
        ]);
    }
}
