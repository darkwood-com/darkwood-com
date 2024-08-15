<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\DailyBattleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: DailyBattleRepository::class)]
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

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'dailyBattles', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Player $player = null;

    // user that lose the fight

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'status', type: Types::INTEGER)]
    private ?int $status = null;

    /**
     * Get id.
     */
    public function getId(): int
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
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set player.
     */
    public function setPlayer(?Player $player = null): void
    {
        $this->player = $player;
    }

    /**
     * Get player.
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }
}
