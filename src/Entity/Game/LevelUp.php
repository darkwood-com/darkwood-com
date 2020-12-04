<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
/**
 * @ORM\Table(name="game_level_up")
 * @ORM\Entity(repositoryClass="App\Repository\Game\LevelUpRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class LevelUp
{
    use \App\Entity\Traits\TimestampTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer")
     */
    private $level;
    /**
     * @var int
     *
     * @ORM\Column(name="xp", type="integer")
     */
    private $xp;
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
