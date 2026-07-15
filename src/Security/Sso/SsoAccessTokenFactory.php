<?php

declare(strict_types=1);

namespace App\Security\Sso;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Mathieu Ledru
 */
final readonly class SsoAccessTokenFactory
{
    private const int EXPIRES_IN_SECONDS = 900;

    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
    ) {}

    /**
     * @param list<string> $roles
     *
     * @return array{token: string, expires_in: int}
     */
    public function create(UserInterface $user, string $audience, array $roles, string $clientId): array
    {
        $token = $this->jwtManager->createFromPayload($user, [
            'aud' => $audience,
            'roles' => $roles,
            'client_id' => $clientId,
        ]);

        return [
            'token' => $token,
            'expires_in' => self::EXPIRES_IN_SECONDS,
        ];
    }
}
