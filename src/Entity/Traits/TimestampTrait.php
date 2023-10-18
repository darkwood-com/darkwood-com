<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait TimestampTrait.
 */
trait TimestampTrait
{
    /**
     * @Gedmo\Timestampable(on="create")
     */
    #[ORM\Column(name: 'created', type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: false)]
    protected DateTimeInterface $created;

    /**
     * @Gedmo\Timestampable(on="update")
     */
    #[ORM\Column(name: 'updated', type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: false)]
    protected DateTimeInterface $updated;

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return self
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
