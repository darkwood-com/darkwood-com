<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: \App\Repository\Game\LevelUpRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_level_up')]
class LevelUp
{
    use TimestampTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'level', type: 'integer')]
    private ?int $level = null;

    #[ORM\Column(name: 'xp', type: 'integer')]
    private ?int $xp = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set level.
     *
     * @param int $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
    }

    /**
     * Get level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set xp.
     *
     * @param int $xp
     */
    public function setXp($xp): void
    {
        $this->xp = $xp;
    }

    /**
     * Get xp.
     *
     * @return int
     */
    public function getXp()
    {
        return $this->xp;
    }
}
