<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Potion.
 *
 * @ORM\Table(name="game_potion")
 * @ORM\Entity(repositoryClass="App\Repository\Game\PotionRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Potion
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
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="life", type="integer")
     */
    private $life;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="potions", fileNameProperty="imageName")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="potion", cascade={"persist", "remove"})
     */
    protected $players;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="currentPotion", cascade={"persist", "remove"})
     */
    protected $currentPotionPlayers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players              = new \Doctrine\Common\Collections\ArrayCollection();
        $this->currentPotionPlayers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add currentPotionPlayer.
     */
    public function addCurrentPotionPlayer(\App\Entity\Game\Player $currentPotionPlayer): void
    {
        $this->currentPotionPlayers[] = $currentPotionPlayer;
    }

    /**
     * Remove currentPotionPlayer.
     */
    public function removeCurrentPotionPlayer(\App\Entity\Game\Player $currentPotionPlayer)
    {
        $this->currentPotionPlayers->removeElement($currentPotionPlayer);
    }

    /**
     * Get currentPotionPlayers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCurrentPotionPlayers()
    {
        return $this->currentPotionPlayers;
    }
}
