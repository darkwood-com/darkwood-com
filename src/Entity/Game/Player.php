<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: \App\Repository\Game\PlayerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'game_player')]
class Player
{
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Sword::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'sword_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Sword $sword = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Armor::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'armor_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Armor $armor = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Potion::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'potion_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Potion $potion = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Enemy::class, inversedBy: 'lastFightPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'last_fight_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Enemy $lastFight = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Enemy::class, inversedBy: 'currentEnemyPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_enemy_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Enemy $currentEnemy = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Classe::class, inversedBy: 'players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'classe_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Classe $classe = null;

    #[ORM\OneToOne(targetEntity: \App\Entity\User::class, inversedBy: 'player', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\User $user = null;

    /**
     * DailyBattles.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Game\DailyBattle>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Game\DailyBattle::class, mappedBy: 'player', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $dailyBattles;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Gem::class, inversedBy: 'equipment1Players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'equipment1_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Gem $equipment1 = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Gem::class, inversedBy: 'equipment2Players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'equipment2_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Gem $equipment2 = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Gem::class, inversedBy: 'equipment3Players', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'equipment3_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Gem $equipment3 = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Sword::class, inversedBy: 'currentSwordPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_sword_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Sword $currentSword = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Potion::class, inversedBy: 'currentPotionPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_potion_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Potion $currentPotion = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Game\Armor::class, inversedBy: 'currentArmorPlayers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'current_armor_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Game\Armor $currentArmor = null;

    #[ORM\Column(name: 'id', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'lifeMin', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $lifeMin = null;

    #[ORM\Column(name: 'lifeMax', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $lifeMax = null;

    #[ORM\Column(name: 'xp', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $xp = null;

    #[ORM\Column(name: 'gold', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $gold = null;

    #[ORM\Column(name: 'strength', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $strength = null;

    #[ORM\Column(name: 'dexterity', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $dexterity = null;

    #[ORM\Column(name: 'vitality', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $vitality = null;

    #[ORM\Column(name: 'equipment1_is_use', type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $equipment1IsUse = null;

    #[ORM\Column(name: 'equipment2_is_use', type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $equipment2IsUse = null;

    #[ORM\Column(name: 'equipment3_is_use', type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    private ?bool $equipment3IsUse = null;

    #[ORM\Column(name: 'daily_battle_victories', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $dailyBattleVictories = null;

    #[ORM\Column(name: 'daily_battle_defeats', type: \Doctrine\DBAL\Types\Types::INTEGER)]
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
     *
     * @return int
     */
    public function getId()
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
     *
     * @return int
     */
    public function getLifeMin()
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
     *
     * @return int
     */
    public function getLifeMax()
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
     *
     * @return int
     */
    public function getXp()
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
     *
     * @return int
     */
    public function getGold()
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
     *
     * @return int
     */
    public function getStrength()
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
     *
     * @return int
     */
    public function getDexterity()
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
     *
     * @return int
     */
    public function getVitality()
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
     *
     * @return bool
     */
    public function getEquipment1IsUse()
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
     *
     * @return bool
     */
    public function getEquipment2IsUse()
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
     *
     * @return bool
     */
    public function getEquipment3IsUse()
    {
        return $this->equipment3IsUse;
    }

    /**
     * Set sword.
     */
    public function setSword(Sword $sword = null): void
    {
        $this->sword = $sword;
    }

    /**
     * Get sword.
     *
     * @return \App\Entity\Game\Sword
     */
    public function getSword()
    {
        return $this->sword;
    }

    /**
     * Set armor.
     */
    public function setArmor(Armor $armor = null): void
    {
        $this->armor = $armor;
    }

    /**
     * Get armor.
     *
     * @return \App\Entity\Game\Armor
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * Set potion.
     */
    public function setPotion(Potion $potion = null): void
    {
        $this->potion = $potion;
    }

    /**
     * Get potion.
     *
     * @return \App\Entity\Game\Potion
     */
    public function getPotion()
    {
        return $this->potion;
    }

    /**
     * Set lastFight.
     */
    public function setLastFight(Enemy $lastFight = null): void
    {
        $this->lastFight = $lastFight;
    }

    /**
     * Get lastFight.
     *
     * @return \App\Entity\Game\Enemy
     */
    public function getLastFight()
    {
        return $this->lastFight;
    }

    /**
     * Set currentEnemy.
     */
    public function setCurrentEnemy(Enemy $currentEnemy = null): void
    {
        $this->currentEnemy = $currentEnemy;
    }

    /**
     * Get currentEnemy.
     *
     * @return \App\Entity\Game\Enemy
     */
    public function getCurrentEnemy()
    {
        return $this->currentEnemy;
    }

    /**
     * Set classe.
     */
    public function setClasse(Classe $classe = null): void
    {
        $this->classe = $classe;
    }

    /**
     * Get classe.
     *
     * @return \App\Entity\Game\Classe
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set user.
     */
    public function setUser(User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User
     */
    public function getUser()
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
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDailyBattles()
    {
        return $this->dailyBattles;
    }

    /**
     * Set equipment1.
     */
    public function setEquipment1(Gem $equipment1 = null): void
    {
        $this->equipment1 = $equipment1;
    }

    /**
     * Get equipment1.
     *
     * @return \App\Entity\Game\Gem
     */
    public function getEquipment1()
    {
        return $this->equipment1;
    }

    /**
     * Set equipment2.
     */
    public function setEquipment2(Gem $equipment2 = null): void
    {
        $this->equipment2 = $equipment2;
    }

    /**
     * Get equipment2.
     *
     * @return \App\Entity\Game\Gem
     */
    public function getEquipment2()
    {
        return $this->equipment2;
    }

    /**
     * Set equipment3.
     */
    public function setEquipment3(Gem $equipment3 = null): void
    {
        $this->equipment3 = $equipment3;
    }

    /**
     * Get equipment3.
     *
     * @return \App\Entity\Game\Gem
     */
    public function getEquipment3()
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
     *
     * @return int
     */
    public function getDailyBattleVictories()
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
     *
     * @return int
     */
    public function getDailyBattleDefeats()
    {
        return $this->dailyBattleDefeats;
    }

    /**
     * Set currentSword.
     */
    public function setCurrentSword(Sword $currentSword = null): void
    {
        $this->currentSword = $currentSword;
    }

    /**
     * Get currentSword.
     *
     * @return \App\Entity\Game\Sword
     */
    public function getCurrentSword()
    {
        return $this->currentSword;
    }

    /**
     * @return Sword
     */
    public function getCurrentDefaultSword()
    {
        return $this->getCurrentSword() ?: $this->getSword();
    }

    /**
     * Set currentPotion.
     */
    public function setCurrentPotion(Potion $currentPotion = null): void
    {
        $this->currentPotion = $currentPotion;
    }

    /**
     * Get currentPotion.
     *
     * @return \App\Entity\Game\Potion
     */
    public function getCurrentPotion()
    {
        return $this->currentPotion;
    }

    /**
     * @return Potion
     */
    public function getCurrentDefaultPotion()
    {
        return $this->getCurrentPotion() ?: $this->getPotion();
    }

    /**
     * Set currentArmor.
     */
    public function setCurrentArmor(Armor $currentArmor = null): void
    {
        $this->currentArmor = $currentArmor;
    }

    /**
     * Get currentArmor.
     *
     * @return \App\Entity\Game\Armor
     */
    public function getCurrentArmor()
    {
        return $this->currentArmor;
    }

    /**
     * @return Armor
     */
    public function getCurrentDefaultArmor()
    {
        return $this->getCurrentArmor() ?: $this->getArmor();
    }
}
