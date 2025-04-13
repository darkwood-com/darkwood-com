<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Repository\Game\EnemyRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: EnemyRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_enemy')]
class Enemy
{
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $title = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'enemies', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'lastFight', cascade: ['persist', 'remove'])]
    protected Collection $lastFightPlayers;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'maxFight', cascade: ['persist', 'remove'])]
    protected Collection $maxFightPlayers;

    /**
     * Players.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'currentEnemy', cascade: ['persist', 'remove'])]
    protected Collection $currentEnemyPlayers;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'gold', type: Types::INTEGER)]
    private ?int $gold = null;

    #[ORM\Column(name: 'xp', type: Types::INTEGER)]
    private ?int $xp = null;

    #[ORM\Column(name: 'life', type: Types::INTEGER)]
    private ?int $life = null;

    #[ORM\Column(name: 'armor', type: Types::INTEGER)]
    private ?int $armor = null;

    #[ORM\Column(name: 'damageMin', type: Types::INTEGER)]
    private ?int $damageMin = null;

    #[ORM\Column(name: 'damageMax', type: Types::INTEGER)]
    private ?int $damageMax = null;

    #[ORM\Column(name: 'hitLuck', type: Types::INTEGER)]
    private ?int $hitLuck = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lastFightPlayers = new ArrayCollection();
        $this->maxFightPlayers = new ArrayCollection();
        $this->currentEnemyPlayers = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set gold.
     *
     * @param int $gold
     */
    public function setGold($gold): void
    {
        $this->gold = $gold;
    }

    /**
     * Get gold.
     */
    public function getGold(): int
    {
        return $this->gold;
    }

    /**
     * Set xp.
     *
     * @param int $xp
     */
    public function setXp($xp): void
    {
        $this->xp = $xp;
    }

    /**
     * Get xp.
     */
    public function getXp(): int
    {
        return $this->xp;
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
     * Set hitLuck.
     *
     * @param int $hitLuck
     */
    public function setHitLuck($hitLuck): void
    {
        $this->hitLuck = $hitLuck;
    }

    /**
     * Get hitLuck.
     */
    public function getHitLuck(): int
    {
        return $this->hitLuck;
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
     * Add lastFightPlayer.
     */
    public function addLastFightPlayer(Player $lastFightPlayer): void
    {
        $this->lastFightPlayers[] = $lastFightPlayer;
    }

    /**
     * Remove lastFightPlayer.
     */
    public function removeLastFightPlayer(Player $lastFightPlayer)
    {
        $this->lastFightPlayers->removeElement($lastFightPlayer);
    }

    /**
     * Get lastFightPlayers.
     */
    public function getLastFightPlayers(): Collection
    {
        return $this->lastFightPlayers;
    }

    /**
     * Add maxFightPlayer.
     */
    public function addMaxFightPlayer(Player $maxFightPlayer): void
    {
        $this->maxFightPlayers[] = $maxFightPlayer;
    }

    /**
     * Remove maxFightPlayer.
     */
    public function removeMaxFightPlayer(Player $maxFightPlayer)
    {
        $this->maxFightPlayers->removeElement($maxFightPlayer);
    }

    /**
     * Get maxFightPlayers.
     */
    public function getMaxFightPlayers(): Collection
    {
        return $this->maxFightPlayers;
    }

    /**
     * Add currentEnemyPlayer.
     */
    public function addCurrentEnemyPlayer(Player $currentEnemyPlayer): void
    {
        $this->currentEnemyPlayers[] = $currentEnemyPlayer;
    }

    /**
     * Remove currentEnemyPlayer.
     */
    public function removeCurrentEnemyPlayer(Player $currentEnemyPlayer)
    {
        $this->currentEnemyPlayers->removeElement($currentEnemyPlayer);
    }

    /**
     * Get currentEnemyPlayers.
     */
    public function getCurrentEnemyPlayers(): Collection
    {
        return $this->currentEnemyPlayers;
    }
}
