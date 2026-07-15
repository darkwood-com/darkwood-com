<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SsoAuthorizationCodeRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SsoAuthorizationCodeRepository::class)]
#[ORM\Table(name: 'sso_authorization_code')]
#[ORM\Index(name: 'idx_sso_authorization_code_expires_at', columns: ['expires_at'])]
class SsoAuthorizationCode
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 64)]
    private string $code = '';

    /**
     * @var array{
     *     user: array{id: int|string, email: string, roles: list<string>},
     *     client_id: string,
     *     redirect_uri: string,
     *     audience: string
     * }
     */
    #[ORM\Column(type: Types::JSON)]
    private array $payload = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return array{
     *     user: array{id: int|string, email: string, roles: list<string>},
     *     client_id: string,
     *     redirect_uri: string,
     *     audience: string
     * }
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array{
     *     user: array{id: int|string, email: string, roles: list<string>},
     *     client_id: string,
     *     redirect_uri: string,
     *     audience: string
     * } $payload
     */
    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
