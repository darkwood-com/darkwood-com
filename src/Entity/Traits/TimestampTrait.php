<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait TimestampTrait.
 */
trait TimestampTrait
{
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE, nullable: false)]
    protected DateTimeInterface $created;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated', type: Types::DATETIME_MUTABLE, nullable: false)]
    protected DateTimeInterface $updated;

    /**
     * Set created.
     *
     * @param DateTime $created
     */
    public function setCreated($created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     */
    public function setUpdated($updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     */
    public function getUpdated(): DateTime
    {
        return $this->updated;
    }
}
