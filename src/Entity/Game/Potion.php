<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="game_potion")
 *
 * @ORM\Entity(repositoryClass="App\Repository\Game\PotionRepository")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @Vich\Uploadable
 */
class Potion
{
    use TimestampTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected ?string $title = null;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="potions", fileNameProperty="imageName")
     */
    protected $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="potion", cascade={"persist", "remove"})
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    protected \Doctrine\Common\Collections\Collection $players;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="currentPotion", cascade={"persist", "remove"})
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    protected \Doctrine\Common\Collections\Collection $currentPotionPlayers;

    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="price", type="integer")
     */
    private ?int $price = null;

    /**
     * @ORM\Column(name="life", type="integer")
     */
    private ?int $life = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->currentPotionPlayers = new ArrayCollection();
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
            $this->updated = new DateTime('now');
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
    public function addPlayer(Player $player): void
    {
        $this->players[] = $player;
    }

    /**
     * Remove player.
     */
    public function removePlayer(Player $player)
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
    public function addCurrentPotionPlayer(Player $currentPotionPlayer): void
    {
        $this->currentPotionPlayers[] = $currentPotionPlayer;
    }

    /**
     * Remove currentPotionPlayer.
     */
    public function removeCurrentPotionPlayer(Player $currentPotionPlayer)
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
