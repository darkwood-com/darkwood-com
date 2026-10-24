<?php

declare(strict_types=1);

namespace App\Security\Oidc;

use App\Entity\User;
use League\Bundle\OAuth2ServerBundle\Converter\UserConverterInterface;
use League\Bundle\OAuth2ServerBundle\Entity\User as LeagueUser;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Maps authenticated Darkwood users to OAuth/OIDC subjects using the immutable UUID.
 *
 * @author Mathieu Ledru
 */
final class UuidUserConverter implements UserConverterInterface
{
    public function toLeague(UserInterface $user): UserEntityInterface
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException(sprintf(
                'OIDC subjects require %s, got %s.',
                User::class,
                $user::class,
            ));
        }

        $userEntity = new LeagueUser();
        $userEntity->setIdentifier($user->getUuid()->toRfc4122());

        return $userEntity;
    }
}
