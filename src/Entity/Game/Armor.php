<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: \App\Repository\Game\ArmorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_armor')]
class Armor
{
    use TimestampTrait;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    protected ?string $title = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'armors', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Game\Player::class, mappedBy: 'armor', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $players;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Game\Player::class, mappedBy: 'currentArmor', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $currentArmorPlayers;

    #[ORM\Column(name: 'id', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'price', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $price = null;

    #[ORM\Column(name: 'armor', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $armor = null;

    #[ORM\Column(name: 'requiredStrength', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $requiredStrength = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->currentArmorPlayers = new ArrayCollection();
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
     * Add currentArmorPlayer.
     */
    public function addCurrentArmorPlayer(Player $currentArmorPlayer): void
    {
        $this->currentArmorPlayers[] = $currentArmorPlayer;
    }

    /**
     * Remove currentArmorPlayer.
     */
    public function removeCurrentArmorPlayer(Player $currentArmorPlayer)
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
