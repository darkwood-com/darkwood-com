<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="game_enemy")
 * @ORM\Entity(repositoryClass="App\Repository\Game\EnemyRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Enemy
{
    use TimestampTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var int
     *
     * @ORM\Column(name="gold", type="integer")
     */
    private $gold;

    /**
     * @var int
     *
     * @ORM\Column(name="xp", type="integer")
     */
    private $xp;

    /**
     * @var int
     *
     * @ORM\Column(name="life", type="integer")
     */
    private $life;

    /**
     * @var int
     *
     * @ORM\Column(name="armor", type="integer")
     */
    private $armor;

    /**
     * @var int
     *
     * @ORM\Column(name="damageMin", type="integer")
     */
    private $damageMin;

    /**
     * @var int
     *
     * @ORM\Column(name="damageMax", type="integer")
     */
    private $damageMax;

    /**
     * @var int
     *
     * @ORM\Column(name="hitLuck", type="integer")
     */
    private $hitLuck;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="enemies", fileNameProperty="imageName")
     */
    protected $image;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageName;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="lastFight", cascade={"persist", "remove"})
     */
    protected $lastFightPlayers;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="currentEnemy", cascade={"persist", "remove"})
     */
    protected $currentEnemyPlayers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lastFightPlayers    = new ArrayCollection();
        $this->currentEnemyPlayers = new ArrayCollection();
    }

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
     * Set gold.
     *
     * @param int $gold
     */
    public function setGold($gold): void
    {
        $this->gold = $gold;
    }

    /**
     * Get gold.
     *
     * @return int
     */
    public function getGold()
    {
        return $this->gold;
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

    /**
     * Set life.
     *
     * @param int $life
     */
    public function setLife($life): void
    {
        $this->life = $life;
    }

    /**
     * Get life.
     *
     * @return int
     */
    public function getLife()
    {
        return $this->life;
    }

    /**
     * Set armor.
     *
     * @param int $armor
     */
    public function setArmor($armor): void
    {
        $this->armor = $armor;
    }

    /**
     * Get armor.
     *
     * @return int
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * Set damageMin.
     *
     * @param int $damageMin
     */
    public function setDamageMin($damageMin): void
    {
        $this->damageMin = $damageMin;
    }

    /**
     * Get damageMin.
     *
     * @return int
     */
    public function getDamageMin()
    {
        return $this->damageMin;
    }

    /**
     * Set damageMax.
     *
     * @param int $damageMax
     */
    public function setDamageMax($damageMax): void
    {
        $this->damageMax = $damageMax;
    }

    /**
     * Get damageMax.
     *
     * @return int
     */
    public function getDamageMax()
    {
        return $this->damageMax;
    }

    /**
     * Set hitLuck.
     *
     * @param int $hitLuck
     */
    public function setHitLuck($hitLuck): void
    {
        $this->hitLuck = $hitLuck;
    }

    /**
     * Get hitLuck.
     *
     * @return int
     */
    public function getHitLuck()
    {
        return $this->hitLuck;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImage(File $image)
    {
        $this->image = $image;
        if ($image) {
            // doctrine listeners event
            $this->updated = new \DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add lastFightPlayer.
     */
    public function addLastFightPlayer(Player $lastFightPlayer): void
    {
        $this->lastFightPlayers[] = $lastFightPlayer;
    }

    /**
     * Remove lastFightPlayer.
     */
    public function removeLastFightPlayer(Player $lastFightPlayer)
    {
        $this->lastFightPlayers->removeElement($lastFightPlayer);
    }

    /**
     * Get lastFightPlayers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLastFightPlayers()
    {
        return $this->lastFightPlayers;
    }

    /**
     * Add currentEnemyPlayer.
     */
    public function addCurrentEnemyPlayer(Player $currentEnemyPlayer): void
    {
        $this->currentEnemyPlayers[] = $currentEnemyPlayer;
    }

    /**
     * Remove currentEnemyPlayer.
     */
    public function removeCurrentEnemyPlayer(Player $currentEnemyPlayer)
    {
        $this->currentEnemyPlayers->removeElement($currentEnemyPlayer);
    }

    /**
     * Get currentEnemyPlayers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCurrentEnemyPlayers()
    {
        return $this->currentEnemyPlayers;
    }
}
