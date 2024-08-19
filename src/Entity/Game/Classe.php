<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\ClasseRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ClasseRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_classe')]
class Classe
{
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $title = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'classes', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'classe', cascade: ['persist', 'remove'])]
    protected Collection $players;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'strength', type: Types::INTEGER)]
    private ?int $strength = null;

    #[ORM\Column(name: 'dexterity', type: Types::INTEGER)]
    private ?int $dexterity = null;

    #[ORM\Column(name: 'vitality', type: Types::INTEGER)]
    private ?int $vitality = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    /**
     * @param int $strength
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;
    }

    public function getDexterity(): int
    {
        return $this->dexterity;
    }

    /**
     * @param int $dexterity
     */
    public function setDexterity($dexterity)
    {
        $this->dexterity = $dexterity;
    }

    public function getVitality(): int
    {
        return $this->vitality;
    }

    /**
     * @param int $vitality
     */
    public function setVitality($vitality)
    {
        $this->vitality = $vitality;
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
}
