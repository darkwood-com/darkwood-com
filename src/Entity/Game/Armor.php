<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\ArmorRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ArmorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_armor')]
class Armor
{
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $title = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'armors', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'armor', cascade: ['persist', 'remove'])]
    protected Collection $players;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'currentArmor', cascade: ['persist', 'remove'])]
    protected Collection $currentArmorPlayers;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'price', type: Types::INTEGER)]
    private ?int $price = null;

    #[ORM\Column(name: 'armor', type: Types::INTEGER)]
    private ?int $armor = null;

    #[ORM\Column(name: 'requiredStrength', type: Types::INTEGER)]
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
     */
    public function getId(): ?int
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
     */
    public function getPrice(): int
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
     */
    public function getArmor(): int
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
     */
    public function getRequiredStrength(): int
    {
        return $this->requiredStrength;
    }

    public function getImage(): mixed
    {
        return $this->image;
    }

    /**
     * @param File|UploadedFile $image
     */
    public function setImage(File $image)
    {
        $this->image = $image;
        if ($image) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    public function getImageName(): ?string
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
     */
    public function getTitle(): string
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
     */
    public function getPlayers(): Collection
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
     */
    public function getCurrentArmorPlayers(): Collection
    {
        return $this->currentArmorPlayers;
    }
}
