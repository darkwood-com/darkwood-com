<?php

namespace App\Entity\Game;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Sword.
 *
 * @ORM\Table(name="game_sword")
 * @ORM\Entity(repositoryClass="App\Repository\Game\SwordRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Sword
{
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
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

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
     * @ORM\Column(name="requiredStrength", type="integer")
     */
    private $requiredStrength;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="swords", fileNameProperty="imageName")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="sword", cascade={"persist", "remove"})
     */
    protected $players;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="currentSword", cascade={"persist", "remove"})
     */
    protected $currentSwordPlayers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players             = new \Doctrine\Common\Collections\ArrayCollection();
        $this->currentSwordPlayers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set price.
     *
     * @param int $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * Get price.
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
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
     * Set requiredStrength.
     *
     * @param int $requiredStrength
     */
    public function setRequiredStrength($requiredStrength): void
    {
        $this->requiredStrength = $requiredStrength;
    }

    /**
     * Get requiredStrength.
     *
     * @return int
     */
    public function getRequiredStrength()
    {
        return $this->requiredStrength;
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
     * Add player.
     */
    public function addPlayer(\App\Entity\Game\Player $player): void
    {
        $this->players[] = $player;
    }

    /**
     * Remove player.
     */
    public function removePlayer(\App\Entity\Game\Player $player)
    {
        $this->players->removeElement($player);
    }

    /**
     * Get players.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * Add currentSwordPlayer.
     */
    public function addCurrentSwordPlayer(\App\Entity\Game\Player $currentSwordPlayer): void
    {
        $this->currentSwordPlayers[] = $currentSwordPlayer;
    }

    /**
     * Remove currentSwordPlayer.
     */
    public function removeCurrentSwordPlayer(\App\Entity\Game\Player $currentSwordPlayer)
    {
        $this->currentSwordPlayers->removeElement($currentSwordPlayer);
    }

    /**
     * Get currentSwordPlayers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCurrentSwordPlayers()
    {
        return $this->currentSwordPlayers;
    }
}
