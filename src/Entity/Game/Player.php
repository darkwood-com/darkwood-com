<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Entity\User;
use App\Repository\Game\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_player')]
class Player
{
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: Sword::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'sword_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Sword $sword = null;

    #[ORM\ManyToOne(targetEntity: Armor::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'armor_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Armor $armor = null;

    #[ORM\ManyToOne(targetEntity: Potion::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'potion_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Potion $potion = null;

    #[ORM\ManyToOne(targetEntity: Enemy::class, inversedBy: 'lastFightPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'last_fight_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Enemy $lastFight = null;

    #[ORM\ManyToOne(targetEntity: Enemy::class, inversedBy: 'maxFightPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'max_fight_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Enemy $maxFight = null;

    #[ORM\ManyToOne(targetEntity: Enemy::class, inversedBy: 'currentEnemyPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_enemy_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Enemy $currentEnemy = null;

    #[ORM\ManyToOne(targetEntity: Classe::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'classe_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Classe $classe = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'player', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?User $user = null;

    /**
     * DailyBattles.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\DailyBattle>
     */
    #[ORM\OneToMany(targetEntity: DailyBattle::class, mappedBy: 'player', cascade: ['persist', 'remove'])]
    protected Collection $dailyBattles;

    #[ORM\ManyToOne(targetEntity: Gem::class, inversedBy: 'equipment1Players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'equipment1_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Gem $equipment1 = null;

    #[ORM\ManyToOne(targetEntity: Gem::class, inversedBy: 'equipment2Players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'equipment2_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Gem $equipment2 = null;

    #[ORM\ManyToOne(targetEntity: Gem::class, inversedBy: 'equipment3Players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'equipment3_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Gem $equipment3 = null;

    #[ORM\ManyToOne(targetEntity: Sword::class, inversedBy: 'currentSwordPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_sword_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Sword $currentSword = null;

    #[ORM\ManyToOne(targetEntity: Potion::class, inversedBy: 'currentPotionPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_potion_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Potion $currentPotion = null;

    #[ORM\ManyToOne(targetEntity: Armor::class, inversedBy: 'currentArmorPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_armor_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Armor $currentArmor = null;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'lifeMin', type: Types::INTEGER)]
    private ?int $lifeMin = null;

    #[ORM\Column(name: 'lifeMax', type: Types::INTEGER)]
    private ?int $lifeMax = null;

    #[ORM\Column(name: 'xp', type: Types::INTEGER)]
    private ?int $xp = null;

    #[ORM\Column(name: 'gold', type: Types::INTEGER)]
    private ?int $gold = null;

    #[ORM\Column(name: 'strength', type: Types::INTEGER)]
    private ?int $strength = null;

    #[ORM\Column(name: 'dexterity', type: Types::INTEGER)]
    private ?int $dexterity = null;

    #[ORM\Column(name: 'vitality', type: Types::INTEGER)]
    private ?int $vitality = null;

    #[ORM\Column(name: 'equipment1_is_use', type: Types::BOOLEAN)]
    private ?bool $equipment1IsUse = null;

    #[ORM\Column(name: 'equipment2_is_use', type: Types::BOOLEAN)]
    private ?bool $equipment2IsUse = null;

    #[ORM\Column(name: 'equipment3_is_use', type: Types::BOOLEAN)]
    private ?bool $equipment3IsUse = null;

    #[ORM\Column(name: 'daily_battle_victories', type: Types::INTEGER)]
    private ?int $dailyBattleVictories = null;

    #[ORM\Column(name: 'daily_battle_defeats', type: Types::INTEGER)]
    private ?int $dailyBattleDefeats = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dailyBattles = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set lifeMin.
     *
     * @param int $lifeMin
     */
    public function setLifeMin($lifeMin): void
    {
        $this->lifeMin = $lifeMin;
    }

    /**
     * Get lifeMin.
     */
    public function getLifeMin(): int
    {
        return $this->lifeMin;
    }

    /**
     * Set lifeMax.
     *
     * @param int $lifeMax
     */
    public function setLifeMax($lifeMax): void
    {
        $this->lifeMax = $lifeMax;
    }

    /**
     * Get lifeMax.
     */
    public function getLifeMax(): int
    {
        return $this->lifeMax;
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
     * Set strength.
     *
     * @param int $strength
     */
    public function setStrength($strength): void
    {
        $this->strength = $strength;
    }

    /**
     * Get strength.
     */
    public function getStrength(): int
    {
        return $this->strength;
    }

    /**
     * Set dexterity.
     *
     * @param int $dexterity
     */
    public function setDexterity($dexterity): void
    {
        $this->dexterity = $dexterity;
    }

    /**
     * Get dexterity.
     */
    public function getDexterity(): int
    {
        return $this->dexterity;
    }

    /**
     * Set vitality.
     *
     * @param int $vitality
     */
    public function setVitality($vitality): void
    {
        $this->vitality = $vitality;
    }

    /**
     * Get vitality.
     */
    public function getVitality(): int
    {
        return $this->vitality;
    }

    /**
     * Set equipment1IsUse.
     *
     * @param bool $equipment1IsUse
     */
    public function setEquipment1IsUse($equipment1IsUse): void
    {
        $this->equipment1IsUse = $equipment1IsUse;
    }

    /**
     * Get equipment1IsUse.
     */
    public function getEquipment1IsUse(): bool
    {
        return $this->equipment1IsUse;
    }

    /**
     * Set equipment2IsUse.
     *
     * @param bool $equipment2IsUse
     */
    public function setEquipment2IsUse($equipment2IsUse): void
    {
        $this->equipment2IsUse = $equipment2IsUse;
    }

    /**
     * Get equipment2IsUse.
     */
    public function getEquipment2IsUse(): bool
    {
        return $this->equipment2IsUse;
    }

    /**
     * Set equipment3IsUse.
     *
     * @param bool $equipment3IsUse
     */
    public function setEquipment3IsUse($equipment3IsUse): void
    {
        $this->equipment3IsUse = $equipment3IsUse;
    }

    /**
     * Get equipment3IsUse.
     */
    public function getEquipment3IsUse(): bool
    {
        return $this->equipment3IsUse;
    }

    /**
     * Set sword.
     */
    public function setSword(?Sword $sword = null): void
    {
        $this->sword = $sword;
    }

    /**
     * Get sword.
     */
    public function getSword(): Sword
    {
        return $this->sword;
    }

    /**
     * Set armor.
     */
    public function setArmor(?Armor $armor = null): void
    {
        $this->armor = $armor;
    }

    /**
     * Get armor.
     */
    public function getArmor(): Armor
    {
        return $this->armor;
    }

    /**
     * Set potion.
     */
    public function setPotion(?Potion $potion = null): void
    {
        $this->potion = $potion;
    }

    /**
     * Get potion.
     */
    public function getPotion(): Potion
    {
        return $this->potion;
    }

    /**
     * Set lastFight.
     */
    public function setLastFight(?Enemy $lastFight = null): void
    {
        $this->lastFight = $lastFight;
    }

    /**
     * Get lastFight.
     */
    public function getLastFight(): ?Enemy
    {
        return $this->lastFight;
    }

    /**
     * Set maxFight.
     */
    public function setMaxFight(?Enemy $maxFight = null): void
    {
        $this->maxFight = $maxFight;
    }

    /**
     * Get maxFight.
     */
    public function getMaxFight(): ?Enemy
    {
        return $this->maxFight;
    }

    /**
     * Set currentEnemy.
     */
    public function setCurrentEnemy(?Enemy $currentEnemy = null): void
    {
        $this->currentEnemy = $currentEnemy;
    }

    /**
     * Get currentEnemy.
     */
    public function getCurrentEnemy(): ?Enemy
    {
        return $this->currentEnemy;
    }

    /**
     * Set classe.
     */
    public function setClasse(?Classe $classe = null): void
    {
        $this->classe = $classe;
    }

    /**
     * Get classe.
     */
    public function getClasse(): Classe
    {
        return $this->classe;
    }

    /**
     * Set user.
     */
    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Get user.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Add dailyBattle.
     */
    public function addDailyBattle(DailyBattle $dailyBattle): void
    {
        $this->dailyBattles[] = $dailyBattle;
    }

    /**
     * Remove dailyBattle.
     */
    public function removeDailyBattle(DailyBattle $dailyBattle)
    {
        $this->dailyBattles->removeElement($dailyBattle);
    }

    /**
     * Get dailyBattles.
     */
    public function getDailyBattles(): Collection
    {
        return $this->dailyBattles;
    }

    /**
     * Set equipment1.
     */
    public function setEquipment1(?Gem $equipment1 = null): void
    {
        $this->equipment1 = $equipment1;
    }

    /**
     * Get equipment1.
     */
    public function getEquipment1(): ?Gem
    {
        return $this->equipment1;
    }

    /**
     * Set equipment2.
     */
    public function setEquipment2(?Gem $equipment2 = null): void
    {
        $this->equipment2 = $equipment2;
    }

    /**
     * Get equipment2.
     */
    public function getEquipment2(): ?Gem
    {
        return $this->equipment2;
    }

    /**
     * Set equipment3.
     */
    public function setEquipment3(?Gem $equipment3 = null): void
    {
        $this->equipment3 = $equipment3;
    }

    /**
     * Get equipment3.
     */
    public function getEquipment3(): ?Gem
    {
        return $this->equipment3;
    }

    /**
     * Set dailyBattleVictories.
     *
     * @param int $dailyBattleVictories
     */
    public function setDailyBattleVictories($dailyBattleVictories): void
    {
        $this->dailyBattleVictories = $dailyBattleVictories;
    }

    /**
     * Get dailyBattleVictories.
     */
    public function getDailyBattleVictories(): int
    {
        return $this->dailyBattleVictories;
    }

    /**
     * Set dailyBattleDefeats.
     *
     * @param int $dailyBattleDefeats
     */
    public function setDailyBattleDefeats($dailyBattleDefeats): void
    {
        $this->dailyBattleDefeats = $dailyBattleDefeats;
    }

    /**
     * Get dailyBattleDefeats.
     */
    public function getDailyBattleDefeats(): int
    {
        return $this->dailyBattleDefeats;
    }

    /**
     * Set currentSword.
     */
    public function setCurrentSword(?Sword $currentSword = null): void
    {
        $this->currentSword = $currentSword;
    }

    /**
     * Get currentSword.
     */
    public function getCurrentSword(): ?Sword
    {
        return $this->currentSword;
    }

    public function getCurrentDefaultSword(): Sword
    {
        return $this->getCurrentSword() ?: $this->getSword();
    }

    /**
     * Set currentPotion.
     */
    public function setCurrentPotion(?Potion $currentPotion = null): void
    {
        $this->currentPotion = $currentPotion;
    }

    /**
     * Get currentPotion.
     */
    public function getCurrentPotion(): ?Potion
    {
        return $this->currentPotion;
    }

    public function getCurrentDefaultPotion(): Potion
    {
        return $this->getCurrentPotion() ?: $this->getPotion();
    }

    /**
     * Set currentArmor.
     */
    public function setCurrentArmor(?Armor $currentArmor = null): void
    {
        $this->currentArmor = $currentArmor;
    }

    /**
     * Get currentArmor.
     */
    public function getCurrentArmor(): ?Armor
    {
        return $this->currentArmor;
    }

    public function getCurrentDefaultArmor(): Armor
    {
        return $this->getCurrentArmor() ?: $this->getArmor();
    }
}
