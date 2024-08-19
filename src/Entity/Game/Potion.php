<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\PotionRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PotionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_potion')]
class Potion
{
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $title = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'potions', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'potion', cascade: ['persist', 'remove'])]
    protected Collection $players;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'currentPotion', cascade: ['persist', 'remove'])]
    protected Collection $currentPotionPlayers;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'price', type: Types::INTEGER)]
    private ?int $price = null;

    #[ORM\Column(name: 'life', type: Types::INTEGER)]
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
     */
    public function getId(): int
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
     */
    public function getLife(): int
    {
        return $this->life;
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
     */
    public function getCurrentPotionPlayers(): Collection
    {
        return $this->currentPotionPlayers;
    }
}
