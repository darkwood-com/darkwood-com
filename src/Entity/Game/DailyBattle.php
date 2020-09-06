<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * DailyBattle.
 *
 * @ORM\Table(name="game_daily_battle")
 * @ORM\Entity(repositoryClass="App\Repository\Game\DailyBattleRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class DailyBattle
{
    use TimestampTrait;

    const STATUS_DAILY_USER = 0;    //user of the day
    const STATUS_NEW_WIN    = 1;        //user that win the fight
    const STATUS_NEW_LOSE   = 2;        //user that lose the fight

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Player", inversedBy="dailyBattles", cascade={"persist"})
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $player;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

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
     *
     * @param \App\Entity\Game\Player $player
     */
    public function setPlayer(\App\Entity\Game\Player $player = null): void
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
