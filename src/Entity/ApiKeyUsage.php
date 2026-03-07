<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApiKeyUsageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiKeyUsageRepository::class)]
#[ORM\Table(name: 'api_key_usage')]
#[ORM\UniqueConstraint(name: 'uniq_api_key_usage_day', columns: ['api_key_id', 'usage_date'])]
class ApiKeyUsage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ApiKey::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ApiKey $apiKey = null;

    #[ORM\Column(name: 'usage_date', type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $date = null;

    #[ORM\Column(name: 'usage_count', type: Types::INTEGER)]
    private int $count = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }

    public function setApiKey(ApiKey $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }
}
