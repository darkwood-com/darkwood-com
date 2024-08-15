<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\LevelUpRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: LevelUpRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_level_up')]
class LevelUp
{
    use TimestampTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'level', type: Types::INTEGER)]
    private ?int $level = null;

    #[ORM\Column(name: 'xp', type: Types::INTEGER)]
    private ?int $xp = null;

    /**
     * Get id.
     */
    public function getId(): int
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
     */
    public function getLevel(): int
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
     */
    public function getXp(): int
    {
        return $this->xp;
    }
}
