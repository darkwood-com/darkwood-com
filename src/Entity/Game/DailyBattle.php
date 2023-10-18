<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: \App\Repository\Game\DailyBattleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_daily_battle')]
class DailyBattle
{
    use TimestampTrait;
    final public const STATUS_DAILY_USER = 0;

    // user of the day
    final public const STATUS_NEW_WIN = 1;

    // user that win the fight
    final public const STATUS_NEW_LOSE = 2;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Player::class, inversedBy: 'dailyBattles', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Player $player = null;

    // user that lose the fight

    #[ORM\Column(name: 'id', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'status', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $status = null;

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
     * Set status.
     *
     * @param int $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set player.
     */
    public function setPlayer(Player $player = null): void
    {
        $this->player = $player;
    }

    /**
     * Get player.
     *
     * @return \App\Entity\Game\Player
     */
    public function getPlayer()
    {
        return $this->player;
    }
}
