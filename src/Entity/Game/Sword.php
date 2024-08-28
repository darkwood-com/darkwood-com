<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\SwordRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: SwordRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_sword')]
class Sword
{
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $title = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'swords', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'sword', cascade: ['persist', 'remove'])]
    protected Collection $players;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'currentSword', cascade: ['persist', 'remove'])]
    protected Collection $currentSwordPlayers;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'price', type: Types::INTEGER)]
    private ?int $price = null;

    #[ORM\Column(name: 'damageMin', type: Types::INTEGER)]
    private ?int $damageMin = null;

    #[ORM\Column(name: 'damageMax', type: Types::INTEGER)]
    private ?int $damageMax = null;

    #[ORM\Column(name: 'requiredStrength', type: Types::INTEGER)]
    private ?int $requiredStrength = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->currentSwordPlayers = new ArrayCollection();
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
     */
    public function getDamageMin(): int
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
     */
    public function getDamageMax(): int
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
     * Add currentSwordPlayer.
     */
    public function addCurrentSwordPlayer(Player $currentSwordPlayer): void
    {
        $this->currentSwordPlayers[] = $currentSwordPlayer;
    }

    /**
     * Remove currentSwordPlayer.
     */
    public function removeCurrentSwordPlayer(Player $currentSwordPlayer)
    {
        $this->currentSwordPlayers->removeElement($currentSwordPlayer);
    }

    /**
     * Get currentSwordPlayers.
     */
    public function getCurrentSwordPlayers(): Collection
    {
        return $this->currentSwordPlayers;
    }
}
