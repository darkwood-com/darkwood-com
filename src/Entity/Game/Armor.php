<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Armor.
 *
 * @ORM\Table(name="game_armor")
 * @ORM\Entity(repositoryClass="App\Repository\Game\ArmorRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Armor
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
     * @ORM\Column(name="armor", type="integer")
     */
    private $armor;
    /**
     * @var int
     *
     * @ORM\Column(name="requiredStrength", type="integer")
     */
    private $requiredStrength;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="armors", fileNameProperty="imageName")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="armor", cascade={"persist", "remove"})
     */
    protected $players;
    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="currentArmor", cascade={"persist", "remove"})
     */
    protected $currentArmorPlayers;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players = new \Doctrine\Common\Collections\ArrayCollection();
        $this->currentArmorPlayers = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function setImage(\Symfony\Component\HttpFoundation\File\File $image)
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
     * Add currentArmorPlayer.
     */
    public function addCurrentArmorPlayer(\App\Entity\Game\Player $currentArmorPlayer): void
    {
        $this->currentArmorPlayers[] = $currentArmorPlayer;
    }
    /**
     * Remove currentArmorPlayer.
     */
    public function removeCurrentArmorPlayer(\App\Entity\Game\Player $currentArmorPlayer)
    {
        $this->currentArmorPlayers->removeElement($currentArmorPlayer);
    }
    /**
     * Get currentArmorPlayers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCurrentArmorPlayers()
    {
        return $this->currentArmorPlayers;
    }
}
