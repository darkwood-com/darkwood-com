<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\GemRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: GemRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_gem')]
class Gem
{
    use TimestampTrait;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'gems', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'equipment1', cascade: ['persist', 'remove'])]
    protected Collection $equipment1Players;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'equipment2', cascade: ['persist', 'remove'])]
    protected Collection $equipment2Players;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'equipment3', cascade: ['persist', 'remove'])]
    protected Collection $equipment3Players;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'power', type: Types::INTEGER)]
    private ?int $power = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->equipment1Players = new ArrayCollection();
        $this->equipment2Players = new ArrayCollection();
        $this->equipment3Players = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set power.
     *
     * @param int $power
     */
    public function setPower($power): void
    {
        $this->power = $power;
    }

    /**
     * Get power.
     */
    public function getPower(): int
    {
        return $this->power;
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
     * Add equipment1Player.
     */
    public function addEquipment1Player(Player $equipment1Player): void
    {
        $this->equipment1Players[] = $equipment1Player;
    }

    /**
     * Remove equipment1Player.
     */
    public function removeEquipment1Player(Player $equipment1Player)
    {
        $this->equipment1Players->removeElement($equipment1Player);
    }

    /**
     * Get equipment1Players.
     */
    public function getEquipment1Players(): Collection
    {
        return $this->equipment1Players;
    }

    /**
     * Add equipment2Player.
     */
    public function addEquipment2Player(Player $equipment2Player): void
    {
        $this->equipment2Players[] = $equipment2Player;
    }

    /**
     * Remove equipment2Player.
     */
    public function removeEquipment2Player(Player $equipment2Player)
    {
        $this->equipment2Players->removeElement($equipment2Player);
    }

    /**
     * Get equipment2Players.
     */
    public function getEquipment2Players(): Collection
    {
        return $this->equipment2Players;
    }

    /**
     * Add equipment3Player.
     */
    public function addEquipment3Player(Player $equipment3Player): void
    {
        $this->equipment3Players[] = $equipment3Player;
    }

    /**
     * Remove equipment3Player.
     */
    public function removeEquipment3Player(Player $equipment3Player)
    {
        $this->equipment3Players->removeElement($equipment3Player);
    }

    /**
     * Get equipment3Players.
     */
    public function getEquipment3Players(): Collection
    {
        return $this->equipment3Players;
    }
}
