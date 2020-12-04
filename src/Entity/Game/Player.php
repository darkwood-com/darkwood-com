<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Table(name="game_player")
 * @ORM\Entity(repositoryClass="App\Repository\Game\PlayerRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Player
{
    use TimestampTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var int
     *
     * @ORM\Column(name="lifeMin", type="integer")
     */
    private $lifeMin;
    /**
     * @var int
     *
     * @ORM\Column(name="lifeMax", type="integer")
     */
    private $lifeMax;
    /**
     * @var int
     *
     * @ORM\Column(name="xp", type="integer")
     */
    private $xp;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Sword", inversedBy="players", cascade={"persist"})
     * @ORM\JoinColumn(name="sword_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $sword;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Armor", inversedBy="players", cascade={"persist"})
     * @ORM\JoinColumn(name="armor_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $armor;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Potion", inversedBy="players", cascade={"persist"})
     * @ORM\JoinColumn(name="potion_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $potion;
    /**
     * @var int
     *
     * @ORM\Column(name="gold", type="integer")
     */
    private $gold;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Enemy", inversedBy="lastFightPlayers", cascade={"persist"})
     * @ORM\JoinColumn(name="last_fight_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $lastFight;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Enemy", inversedBy="currentEnemyPlayers", cascade={"persist"})
     * @ORM\JoinColumn(name="current_enemy_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $currentEnemy;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Classe", inversedBy="players", cascade={"persist"})
     * @ORM\JoinColumn(name="classe_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $classe;
    /**
     * @var int
     *
     * @ORM\Column(name="strength", type="integer")
     */
    private $strength;
    /**
     * @var int
     *
     * @ORM\Column(name="dexterity", type="integer")
     */
    private $dexterity;
    /**
     * @var int
     *
     * @ORM\Column(name="vitality", type="integer")
     */
    private $vitality;
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="player", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $user;
    /**
     * DailyBattles.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\DailyBattle", mappedBy="player", cascade={"persist", "remove"})
     */
    protected $dailyBattles;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Gem", inversedBy="equipment1Players", cascade={"persist"})
     * @ORM\JoinColumn(name="equipment1_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $equipment1;
    /**
     * @var bool
     *
     * @ORM\Column(name="equipment1_is_use", type="boolean")
     */
    private $equipment1IsUse;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Gem", inversedBy="equipment2Players", cascade={"persist"})
     * @ORM\JoinColumn(name="equipment2_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $equipment2;
    /**
     * @var bool
     *
     * @ORM\Column(name="equipment2_is_use", type="boolean")
     */
    private $equipment2IsUse;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Gem", inversedBy="equipment3Players", cascade={"persist"})
     * @ORM\JoinColumn(name="equipment3_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $equipment3;
    /**
     * @var bool
     *
     * @ORM\Column(name="equipment3_is_use", type="boolean")
     */
    private $equipment3IsUse;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Sword", inversedBy="currentSwordPlayers", cascade={"persist"})
     * @ORM\JoinColumn(name="current_sword_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $currentSword;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Potion", inversedBy="currentPotionPlayers", cascade={"persist"})
     * @ORM\JoinColumn(name="current_potion_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $currentPotion;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game\Armor", inversedBy="currentArmorPlayers", cascade={"persist"})
     * @ORM\JoinColumn(name="current_armor_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $currentArmor;
    /**
     * @var int
     *
     * @ORM\Column(name="daily_battle_victories", type="integer")
     */
    private $dailyBattleVictories;
    /**
     * @var int
     *
     * @ORM\Column(name="daily_battle_defeats", type="integer")
     */
    private $dailyBattleDefeats;
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
     *
     * @param \App\Entity\Game\Sword $sword
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
     *
     * @param \App\Entity\Game\Armor $armor
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
     *
     * @param \App\Entity\Game\Potion $potion
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
     *
     * @param \App\Entity\Game\Enemy $lastFight
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
     *
     * @param \App\Entity\Game\Enemy $currentEnemy
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
     *
     * @param \App\Entity\Game\Classe $classe
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
     *
     * @param \App\Entity\User $user
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
     *
     * @param \App\Entity\Game\Gem $equipment1
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
     *
     * @param \App\Entity\Game\Gem $equipment2
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
     *
     * @param \App\Entity\Game\Gem $equipment3
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
     *
     * @param \App\Entity\Game\Sword $currentSword
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
        return $this->getCurrentSword() ? $this->getCurrentSword() : $this->getSword();
    }
    /**
     * Set currentPotion.
     *
     * @param \App\Entity\Game\Potion $currentPotion
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
        return $this->getCurrentPotion() ? $this->getCurrentPotion() : $this->getPotion();
    }
    /**
     * Set currentArmor.
     *
     * @param \App\Entity\Game\Armor $currentArmor
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
        return $this->getCurrentArmor() ? $this->getCurrentArmor() : $this->getArmor();
    }
}
