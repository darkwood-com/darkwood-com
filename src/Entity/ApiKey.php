<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\ApiKeyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Table(name: 'api_key')]
class ApiKey
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /** SHA-256 hash of the raw key (64 hex chars). */
    #[ORM\Column(type: Types::STRING, length: 64, unique: true)]
    private string $keyHash = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isBeta = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isPremium = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $dailyActionLimit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyHash(): string
    {
        return $this->keyHash;
    }

    public function setKeyHash(string $keyHash): static
    {
        $this->keyHash = $keyHash;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isBeta(): bool
    {
        return $this->isBeta;
    }

    public function setIsBeta(bool $isBeta): static
    {
        $this->isBeta = $isBeta;

        return $this;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function setIsPremium(bool $isPremium): static
    {
        $this->isPremium = $isPremium;

        return $this;
    }

    public function getDailyActionLimit(): ?int
    {
        return $this->dailyActionLimit;
    }

    public function setDailyActionLimit(?int $dailyActionLimit): static
    {
        $this->dailyActionLimit = $dailyActionLimit;

        return $this;
    }
}
