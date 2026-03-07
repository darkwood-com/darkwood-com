<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DarkwoodArchiveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DarkwoodArchiveRepository::class)]
#[ORM\Table(name: 'game_archive')]
#[ORM\UniqueConstraint(name: 'uniq_darkwood_archive_date', columns: ['archive_date'])]
class DarkwoodArchive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /** UTC date for this archive (YYYY-MM-DD). */
    #[ORM\Column(type: Types::DATE_IMMUTABLE, unique: true)]
    private ?\DateTimeImmutable $archiveDate = null;

    /** Snapshot payload (same shape as /api/darkwood/state response). */
    #[ORM\Column(type: Types::JSON)]
    private array $payload = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArchiveDate(): ?\DateTimeImmutable
    {
        return $this->archiveDate;
    }

    public function setArchiveDate(\DateTimeImmutable $archiveDate): static
    {
        $this->archiveDate = $archiveDate;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /** API-facing id (YYYY-MM-DD). */
    public function getDateId(): string
    {
        return $this->archiveDate->format('Y-m-d');
    }
}
