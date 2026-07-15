<?php

declare(strict_types=1);

namespace App\Security\Sso;

use App\Entity\SsoAuthorizationCode;
use App\Repository\SsoAuthorizationCodeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Mathieu Ledru
 */
final readonly class SsoAuthorizationCodeService
{
    private const int TTL_SECONDS = 300;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SsoAuthorizationCodeRepository $authorizationCodes,
    ) {}

    /**
     * @param array{id: int|string, email: string, roles: list<string>} $user
     */
    public function issueCode(array $user, string $clientId, string $redirectUri, string $audience): string
    {
        $code = bin2hex(random_bytes(32));
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify(sprintf('+%d seconds', self::TTL_SECONDS));

        $authorizationCode = (new SsoAuthorizationCode())
            ->setCode($code)
            ->setPayload([
                'user' => $user,
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'audience' => $audience,
            ])
            ->setExpiresAt($expiresAt)
            ->setCreatedAt($now);

        $this->entityManager->persist($authorizationCode);
        $this->entityManager->flush();

        return $code;
    }

    /**
     * @return array{
     *     user: array{id: int|string, email: string, roles: list<string>},
     *     client_id: string,
     *     redirect_uri: string,
     *     audience: string
     * }|null
     */
    public function consumeCode(string $code): ?array
    {
        $now = new DateTimeImmutable();

        return $this->entityManager->wrapInTransaction(function () use ($code, $now): ?array {
            $authorizationCode = $this->authorizationCodes->findOneValidForUpdate($code, $now);
            if (null === $authorizationCode) {
                return null;
            }

            $payload = $authorizationCode->getPayload();

            $this->entityManager->remove($authorizationCode);
            $this->entityManager->flush();

            return $payload;
        });
    }
}
