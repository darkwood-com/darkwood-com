<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\CommentPage;
use App\Entity\Game\Armor;
use App\Entity\Game\Classe;
use App\Entity\Game\DailyBattle;
use App\Entity\Game\Enemy;
use App\Entity\Game\Gem;
use App\Entity\Game\LevelUp;
use App\Entity\Game\Player;
use App\Entity\Game\Potion;
use App\Entity\Game\Sword;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\Game\ArmorRepository;
use App\Repository\Game\ClasseRepository;
use App\Repository\Game\DailyBattleRepository;
use App\Repository\Game\EnemyRepository;
use App\Repository\Game\GemRepository;
use App\Repository\Game\LevelUpRepository;
use App\Repository\Game\PlayerRepository;
use App\Repository\Game\PotionRepository;
use App\Repository\Game\SwordRepository;
use App\Validator\Constraints\PaginationDTO;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;
use function in_array;
use function intval;
use function is_array;
use function sprintf;

/**
 * Class GameService.
 *
 * Object manager of newsTranslation.
 */
class GameService
{
    final public const RATIO_EQUIPMENT = 4.0;

    final public const RATIO_HIT_LUCK = 5.0;

    final public const POINTS_BY_LEVEL = 15;

    final public const LIFE_BY_VITALITY = 25;

    final public const DEATH_LOSE_STATS = 10.0;

    protected ArmorRepository $armorRepository;

    protected ClasseRepository $classeRepository;

    protected DailyBattleRepository $dailyBattleRepository;

    protected EnemyRepository $enemyRepository;

    protected GemRepository $gemRepository;

    protected LevelUpRepository $levelUpRepository;

    protected PlayerRepository $playerRepository;

    protected PotionRepository $potionRepository;

    protected SwordRepository $swordRepository;

    public function __construct(
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
        protected EntityManagerInterface $em,
        protected UserService $userService,
        protected PageService $pageService,
        protected CommentService $commentService,
        protected PaginatorInterface $paginator,
        protected FormFactoryInterface $formFactory,
        protected CsrfTokenManagerInterface $tokenManager,
        protected RouterInterface $router,
        protected TokenStorageInterface $tokenStorage
    ) {
        /** @var ArmorRepository $armorRepo */
        $armorRepo = $em->getRepository(Armor::class);
        $this->armorRepository = $armorRepo;

        /** @var ClasseRepository $classeRepo */
        $classeRepo = $em->getRepository(Classe::class);
        $this->classeRepository = $classeRepo;

        /** @var DailyBattleRepository $dailyBattleRepo */
        $dailyBattleRepo = $em->getRepository(DailyBattle::class);
        $this->dailyBattleRepository = $dailyBattleRepo;

        /** @var EnemyRepository $enemyRepo */
        $enemyRepo = $em->getRepository(Enemy::class);
        $this->enemyRepository = $enemyRepo;

        /** @var GemRepository $gemRepo */
        $gemRepo = $em->getRepository(Gem::class);
        $this->gemRepository = $gemRepo;

        /** @var LevelUpRepository $levelUpRepo */
        $levelUpRepo = $em->getRepository(LevelUp::class);
        $this->levelUpRepository = $levelUpRepo;

        /** @var PlayerRepository $playerRepo */
        $playerRepo = $em->getRepository(Player::class);
        $this->playerRepository = $playerRepo;

        /** @var PotionRepository $potionRepo */
        $potionRepo = $em->getRepository(Potion::class);
        $this->potionRepository = $potionRepo;

        /** @var SwordRepository $swordRepo */
        $swordRepo = $em->getRepository(Sword::class);
        $this->swordRepository = $swordRepo;
    }

    public function getOrCreate(User $user): Player
    {
        $player = $user->getPlayer();
        if (!$player) {
            $player = new Player();
            $player->setLifeMin(50);
            $player->setLifeMax(50);
            $player->setXp(0);
            $player->setSword($this->swordRepository->findDefault());
            $player->setArmor($this->armorRepository->findDefault());
            $player->setPotion($this->potionRepository->findDefault());
            $player->setGold(25);
            $player->setClasse($this->classeRepository->findDefault());
            $player->setStrength(0);
            $player->setDexterity(0);
            $player->setVitality(0);
            $player->setDailyBattleVictories(0);
            $player->setDailyBattleDefeats(0);
            $player->setEquipment1IsUse(false);
            $player->setEquipment2IsUse(false);
            $player->setEquipment3IsUse(false);
            $player->setUser($user);
            $user->setPlayer($player);
            $this->userService->save($user);
        }

        return $player;
    }

    public function getInfo(User $user)
    {
        $player = $this->getOrCreate($user);
        $level = $this->levelUpRepository->findByXp($player->getXp());
        if (!$level) {
            $level = new LevelUp();
            $level->setXp(PHP_INT_MAX);
            $level->setLevel(100);
        }

        $damage = [
            'min' => intval($player->getSword()->getDamageMin() * (1 + $player->getStrength() / (self::RATIO_EQUIPMENT * 100.0))),
            'max' => intval($player->getSword()->getDamageMax() * (1 + $player->getStrength() / (self::RATIO_EQUIPMENT * 100.0))),
        ];
        $equipmentDamage = 0;
        if ($player->getEquipment1IsUse()) {
            $gem = $player->getEquipment1();
            if ($gem) {
                $equipmentDamage += $gem->getPower();
            }
        }

        if ($player->getEquipment2IsUse()) {
            $gem = $player->getEquipment2();
            if ($gem) {
                $equipmentDamage += $gem->getPower();
            }
        }

        if ($player->getEquipment3IsUse()) {
            $gem = $player->getEquipment3();
            if ($gem) {
                $equipmentDamage += $gem->getPower();
            }
        }

        $damage['min'] += $equipmentDamage;
        $damage['max'] += $equipmentDamage;
        $swordDamage = intval($player->getStrength() / self::RATIO_EQUIPMENT);
        $hitLuck = intval($player->getDexterity() / self::RATIO_HIT_LUCK);
        $armor = intval($player->getArmor()->getArmor() * (1 + $player->getStrength() / (self::RATIO_EQUIPMENT * 100.0)));
        $armorDefence = intval($player->getStrength() / self::RATIO_EQUIPMENT);
        $points = ['total' => $player->getStrength() + $player->getVitality() + $player->getDexterity(), 'max' => ($level->getLevel() - 1) * self::POINTS_BY_LEVEL];
        $points['diff'] = $points['max'] - $points['total'];
        $life = ['min' => $player->getLifeMin(), 'max' => $player->getLifeMax()];
        $life['diff'] = $life['max'] - $life['min'];

        return [
            'user' => $user,
            'player' => $player,
            'level' => $level,
            'damage' => $damage,
            'swordDamage' => $swordDamage,
            'equipmentDamage' => $equipmentDamage,
            'hitLuck' => $hitLuck,
            'armor' => $armor,
            'armorDefence' => $armorDefence,
            'points' => $points,
            'life' => $life,
        ];
    }

    public function getArmorInfo(Armor $armor)
    {
        return ['armor' => $armor, 'sellPrice' => intval($armor->getPrice() / 2), 'next' => $this->armorRepository->findNext($armor), 'previous' => $this->armorRepository->findPrevious($armor)];
    }

    public function getPotionInfo(Potion $potion)
    {
        return ['potion' => $potion, 'next' => $this->potionRepository->findNext($potion), 'previous' => $this->potionRepository->findPrevious($potion)];
    }

    public function getSwordInfo(Sword $sword)
    {
        return ['sword' => $sword, 'sellPrice' => intval($sword->getPrice() / 2), 'next' => $this->swordRepository->findNext($sword), 'previous' => $this->swordRepository->findPrevious($sword)];
    }

    public function getEnemyInfo(Enemy $enemy)
    {
        return ['enemy' => $enemy, 'next' => $this->enemyRepository->findNext($enemy), 'previous' => $this->enemyRepository->findPrevious($enemy)];
    }

    public function getClasses()
    {
        return ['default' => $this->classeRepository->findDefault(), 'list' => $this->classeRepository->findList()];
    }

    public function getRegenerations(User $user)
    {
        $player = $this->getOrCreate($user);
        $regeneration = [];
        for ($i = 0; $i < 4; $i++) {
            $life = $player->getLifeMax();
            $price = $player->getLifeMax();
            switch ($i) {
                case 0:
                    $life /= 1;
                    $price /= 2;

                    break;
                case 1:
                    $life /= 2;
                    $price /= 6;

                    break;
                case 2:
                    $life /= 4;
                    $price /= 16;

                    break;
                case 3:
                    $life /= 8;
                    $price /= 40;

                    break;
            }

            $regeneration['regeneration' . $i] = ['life' => intval($life), 'price' => intval($price)];
        }

        return $regeneration;
    }

    public function chooseClasse(User $user, $classeId)
    {
        $classeId = (int) $classeId;
        $classe = $this->getClasses();
        $classe = current(array_filter($classe['list'], static fn ($c) => $c->getId() === $classeId));
        if (!$classe) {
            return;
        }

        $player = $this->getOrCreate($user);
        $player->setClasse($classe);

        $this->em->flush();
    }

    public function addPoint(User $user, $type)
    {
        $points = $this->getInfo($user);
        $points = $points['points'];
        if (!in_array($type, ['strength', 'dexterity', 'vitality'], true) || $points['diff'] <= 0) {
            return;
        }

        $player = $this->getOrCreate($user);
        if ($type === 'strength') {
            $player->setStrength($player->getStrength() + 1);
        }

        if ($type === 'dexterity') {
            $player->setDexterity($player->getDexterity() + 1);
        }

        if ($type === 'vitality') {
            $player->setVitality($player->getVitality() + 1);
        }

        if ($type === 'vitality') {
            $classe = $player->getClasse();
            $player->setLifeMin($player->getLifeMin() + $classe->getVitality() * self::LIFE_BY_VITALITY);
            $player->setLifeMax($player->getLifeMax() + $classe->getVitality() * self::LIFE_BY_VITALITY);
        }

        $this->em->flush();
    }

    public function equipGem(User $user, $index)
    {
        $index = (int) $index;
        $player = $this->getOrCreate($user);
        if ($index === 1 && $player->getEquipment1()) {
            $player->setEquipment1IsUse(true);
        } elseif ($index === 2 && $player->getEquipment2()) {
            $player->setEquipment2IsUse(true);
        } elseif ($index === 3 && $player->getEquipment3()) {
            $player->setEquipment3IsUse(true);
        }

        $this->em->flush();
    }

    public function throwGem(User $user, $index)
    {
        $index = (int) $index;
        $player = $this->getOrCreate($user);
        if ($index === 1) {
            $player->setEquipment1(null);
            $player->setEquipment1IsUse(false);
        } elseif ($index === 2) {
            $player->setEquipment2(null);
            $player->setEquipment2IsUse(false);
        } elseif ($index === 3) {
            $player->setEquipment3(null);
            $player->setEquipment3IsUse(false);
        }

        $this->em->flush();
    }

    public function regen(User $user, $key)
    {
        $player = $this->getOrCreate($user);
        $regeneration = $this->getRegenerations($user);
        if (!isset($regeneration[$key])) {
            return;
        }

        $regeneration = $regeneration[$key];
        $newGold = $player->getGold() - $regeneration['price'];
        if ($newGold < 0) {
            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_gold_alert'));

            return;
        }

        $newLife = $player->getLifeMin() + $regeneration['life'];
        $player->setLifeMin($newLife);
        if ($newLife > $player->getLifeMax()) {
            $player->setLifeMin($player->getLifeMax());
        }

        $player->setGold($newGold);
        $this->em->flush();
    }

    public function nextArmor(User $user)
    {
        $player = $this->getOrCreate($user);
        $armorInfo = $this->getArmorInfo($player->getCurrentDefaultArmor());
        if ($armorInfo['next']) {
            $player->setCurrentArmor($armorInfo['next']);
        }

        $this->em->flush();
    }

    public function previousArmor(User $user)
    {
        $player = $this->getOrCreate($user);
        $armorInfo = $this->getArmorInfo($player->getCurrentDefaultArmor());
        if ($armorInfo['previous']) {
            $player->setCurrentArmor($armorInfo['previous']);
        }

        $this->em->flush();
    }

    public function buyArmor(User $user)
    {
        $player = $this->getOrCreate($user);
        $armor = $player->getCurrentDefaultArmor();
        if ($player->getStrength() < $armor->getRequiredStrength()) {
            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_strength_alert'));

            return;
        }

        $newGold = $player->getGold() - $armor->getPrice();
        if ($newGold < 0) {
            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_gold_alert'));

            return;
        }

        $player->setGold($newGold);
        $player->setArmor($armor);

        $this->em->flush();
    }

    public function sellArmor(User $user)
    {
        $player = $this->getOrCreate($user);
        $armorInfo = $this->getArmorInfo($player->getArmor());
        $newGold = $player->getGold() + $armorInfo['sellPrice'];
        $player->setGold($newGold);
        $player->setArmor($this->armorRepository->findDefault());

        $this->em->flush();
    }

    public function nextPotion(User $user)
    {
        $player = $this->getOrCreate($user);
        $potionInfo = $this->getPotionInfo($player->getCurrentDefaultPotion());
        if ($potionInfo['next']) {
            $player->setCurrentPotion($potionInfo['next']);
        }

        $this->em->flush();
    }

    public function previousPotion(User $user)
    {
        $player = $this->getOrCreate($user);
        $potionInfo = $this->getPotionInfo($player->getCurrentDefaultPotion());
        if ($potionInfo['previous']) {
            $player->setCurrentPotion($potionInfo['previous']);
        }

        $this->em->flush();
    }

    public function buyPotion(User $user)
    {
        $player = $this->getOrCreate($user);
        $potion = $player->getCurrentDefaultPotion();
        $newGold = $player->getGold() - $potion->getPrice();
        if ($newGold < 0) {
            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_gold_alert'));

            return;
        }

        $player->setGold($newGold);
        $player->setPotion($potion);

        $this->em->flush();
    }

    public function nextSword(User $user)
    {
        $player = $this->getOrCreate($user);
        $swordInfo = $this->getSwordInfo($player->getCurrentDefaultSword());
        if ($swordInfo['next']) {
            $player->setCurrentSword($swordInfo['next']);
        }

        $this->em->flush();
    }

    public function previousSword(User $user)
    {
        $player = $this->getOrCreate($user);
        $swordInfo = $this->getSwordInfo($player->getCurrentDefaultSword());
        if ($swordInfo['previous']) {
            $player->setCurrentSword($swordInfo['previous']);
        }

        $this->em->flush();
    }

    public function buySword(User $user)
    {
        $player = $this->getOrCreate($user);
        $sword = $player->getCurrentDefaultSword();
        if ($player->getStrength() < $sword->getRequiredStrength()) {
            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_strength_alert'));

            return;
        }

        $newGold = $player->getGold() - $sword->getPrice();
        if ($newGold < 0) {
            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_gold_alert'));

            return;
        }

        $player->setGold($newGold);
        $player->setSword($sword);
        $player->setEquipment1(null);
        $player->setEquipment1IsUse(false);
        $player->setEquipment2(null);
        $player->setEquipment2IsUse(false);
        $player->setEquipment3(null);
        $player->setEquipment3IsUse(false);

        $this->em->flush();
    }

    public function sellSword(User $user)
    {
        $player = $this->getOrCreate($user);
        $swordInfo = $this->getSwordInfo($player->getSword());
        $newGold = $player->getGold() + $swordInfo['sellPrice'];
        $player->setGold($newGold);
        $player->setSword($this->swordRepository->findDefault());
        $player->setEquipment1(null);
        $player->setEquipment1IsUse(false);
        $player->setEquipment2(null);
        $player->setEquipment2IsUse(false);
        $player->setEquipment3(null);
        $player->setEquipment3IsUse(false);

        $this->em->flush();
    }

    public function nextEnemy(User $user)
    {
        $player = $this->getOrCreate($user);
        $enemyInfo = $this->getEnemyInfo($player->getCurrentEnemy() ?: $this->enemyRepository->findDefault());
        if ($enemyInfo['next']) {
            $player->setCurrentEnemy($enemyInfo['next']);
        }

        $this->em->flush();
    }

    public function previousEnemy(User $user)
    {
        $player = $this->getOrCreate($user);
        $enemyInfo = $this->getEnemyInfo($player->getCurrentEnemy() ?: $this->enemyRepository->findDefault());
        if ($enemyInfo['previous']) {
            $player->setCurrentEnemy($enemyInfo['previous']);
        }

        $this->em->flush();
    }

    public function setLastFight(User $user)
    {
        $player = $this->getOrCreate($user);
        if (!$player->getLastFight()) {
            $player->setLastFight($player->getCurrentEnemy() ?: $this->enemyRepository->findDefault());
            $this->em->flush();
        }
    }

    public function getSession(User $user)
    {
        $player = $this->getOrCreate($user);
        $sessionKey = 'fight:' . $player->getId();
        $session = $this->requestStack->getSession()->get($sessionKey);
        $enemy = $player->getLastFight();
        if (!is_array($session) && $enemy) {
            $session = ['player_life_lose' => 0, 'enemy_current_life' => $enemy->getLife(), 'enemy_life_lose' => 0];
        }

        return $session;
    }

    public function setSession(User $user, $value): void
    {
        $player = $this->getOrCreate($user);
        $sessionKey = 'fight:' . $player->getId();
        $this->requestStack->getSession()->set($sessionKey, $value);
    }

    public function fight(User $user, $action)
    {
        $player = $this->getOrCreate($user);
        $playerInfo = $this->getInfo($user);
        $enemy = $player->getLastFight();
        $session = $this->getSession($user);
        $playerAttack = 0;
        if ($action === 'potion') {
            // player use potion
            $potion = $player->getPotion();
            $lifeAdd = $potion->getLife() + $player->getLifeMin();
            if ($lifeAdd > $player->getLifeMax()) {
                $player->setLifeMin($player->getLifeMax());
            } else {
                $player->setLifeMin($lifeAdd);
            }

            $player->setPotion($this->potionRepository->findDefault());
        } else {
            // player attack
            $playerAttack = random_int((int) $playerInfo['damage']['min'], (int) $playerInfo['damage']['max']) - random_int(0, $enemy->getArmor());
        }

        // enemy attack
        $enemyAttack = random_int($enemy->getDamageMin(), $enemy->getDamageMax()) - random_int(0, (int) $playerInfo['armor']);
        if ($enemyAttack > 0) {
            $player->setLifeMin($player->getLifeMin() - $enemyAttack);
        } else {
            $enemyAttack = 0;
        }

        $luck = random_int(1, 100);
        $hitLuck = $enemy->getHitLuck() + $playerInfo['hitLuck'];
        if ($luck > $hitLuck && $action !== 'potion') {
            $playerAttack = -1;
        } else {
            $session['enemy_current_life'] -= $playerAttack;
        }

        $session['player_life_lose'] = $enemyAttack;
        $session['enemy_life_lose'] = $playerAttack;
        $this->em->flush();
        $this->setSession($user, $session);
    }

    public function endFight(User $user)
    {
        $player = $this->getOrCreate($user);
        $playerInfo = $this->getInfo($user);
        $enemy = $player->getLastFight();
        $session = $this->getSession($user);
        $result = ['mode' => null];
        if ($player->getLifeMin() <= 0) {
            $player->setLastFight(null);
            $session = null;
            $result = ['lose_xp' => 0, 'lose_gold' => 0, 'enemy' => $enemy, 'lose_stats' => self::DEATH_LOSE_STATS];
            $result['lose_xp'] = (int) (self::DEATH_LOSE_STATS * $enemy->getXp());
            $result['lose_gold'] = (int) (self::DEATH_LOSE_STATS * $enemy->getGold());
            $player->setLifeMin($player->getLifeMax());
            $player->setXp($player->getXp() - $result['lose_xp']);
            $player->setGold($player->getGold() - $result['lose_gold']);
            if ($player->getXp() < 0) {
                $player->setXp(0);
            }

            if ($player->getGold() < 0) {
                $player->setGold(0);
            }

            $result = ['mode' => 'player_death', 'result' => $result];
        } elseif ($session['enemy_current_life'] <= 0) {
            $player->setLastFight(null);
            // set player max fight
            $maxEnemy = $player->getMaxFight();
            if ($maxEnemy === null || $enemy->getXp() > $maxEnemy->getXp()) {
                $player->setMaxFight($enemy);
            }

            $session = null;
            $result = ['gem' => 'not_found', 'level_up' => false, 'enemy' => $enemy];
            $oldLevel = $playerInfo['level'];
            $player->setXp($player->getXp() + $enemy->getXp());
            $player->setGold($player->getGold() + $enemy->getGold());
            $newLevel = $this->levelUpRepository->findByXp($player->getXp());
            $result['level_up'] = !$newLevel || $newLevel->getLevel() - $oldLevel->getLevel() > 0;
            $findGemLuck = random_int(0, 2);
            // 1 chance on 3 to find a gem
            if ($findGemLuck === 0) {
                // random select a gem proportionnal to enemy level
                $gems = $this->gemRepository->findOrdered();
                $enemies = $this->enemyRepository->findOrdered();
                $enemyPosition = 1;
                foreach ($enemies as $e) {
                    if ($e->getId() === $enemy->getId()) {
                        break;
                    }

                    $enemyPosition++;
                }

                $gemPosition = intval((count($gems) - 1) * random_int(1, $enemyPosition) / count($enemies));
                // find gem
                $gem = current($gems);
                foreach ($gems as $g) {
                    $gem = $g;
                    if ($gemPosition <= 0) {
                        break;
                    }

                    $gemPosition--;
                }

                $result['gem'] = 'found';
                $result['gem_item'] = $gem;
                if (!$player->getEquipment1()) {
                    $player->setEquipment1($gem);
                } elseif (!$player->getEquipment2()) {
                    $player->setEquipment2($gem);
                } elseif (!$player->getEquipment3()) {
                    $player->setEquipment3($gem);
                } else {
                    // no more place to hase a new gem
                    $result['gem'] = 'no_place';
                    $result['gem_item'] = $this->gemRepository->findDefault();
                }
            }

            $result = ['mode' => 'player_win', 'result' => $result];
        }

        $this->em->flush();
        $this->setSession($user, $session);

        return $result;
    }

    public function getOrCreateDailyEnemy(): User
    {
        $now = new DateTime();
        $dailyBattle = $this->dailyBattleRepository->findDaily($now);
        if ($dailyBattle) {
            return $dailyBattle->getPlayer()->getUser();
        }

        // search a random player
        $player = $this->playerRepository->findRand();
        $dailyBattle = new DailyBattle();
        $dailyBattle->setStatus(DailyBattle::STATUS_DAILY_USER);
        $dailyBattle->setPlayer($player);

        $this->em->persist($dailyBattle);
        $this->em->flush();

        return $player->getUser();
    }

    public function getDailyBattles()
    {
        $now = new DateTime();
        $dailyBattles = $this->dailyBattleRepository->findDailyBattles($now);

        return array_map(fn ($dailyBattle) => ['info' => $this->getInfo($dailyBattle->getPlayer()->getUser()), 'dailyBattle' => $dailyBattle], $dailyBattles);
    }

    public function getSessionDaily(User $user)
    {
        $player = $this->getOrCreate($user);
        $sessionDailyKey = 'fightDaily:' . $player->getId();
        $sessionDaily = $this->requestStack->getSession()->get($sessionDailyKey);
        $enemy = $this->getOrCreateDailyEnemy();
        if (!is_array($sessionDaily) && $enemy) {
            $sessionDaily = ['player_current_life' => $user->getPlayer()->getLifeMax(), 'player_life_lose' => 0, 'enemy_current_life' => $enemy->getPlayer()->getLifeMax(), 'enemy_life_lose' => 0];
        }

        return $sessionDaily;
    }

    public function setSessionDaily(User $user, $value): void
    {
        $player = $this->getOrCreate($user);
        $sessionDailyKey = 'fightDaily:' . $player->getId();
        $this->requestStack->getSession()->set($sessionDailyKey, $value);
    }

    public function fightDaily(User $user)
    {
        $player = $this->getOrCreate($user);
        $playerInfo = $this->getInfo($user);
        $enemy = $this->getOrCreateDailyEnemy();
        $enemyInfo = $this->getInfo($enemy);
        $sessionDaily = $this->getSessionDaily($user);
        // player attack
        $playerAttack = random_int((int) $playerInfo['damage']['min'], (int) $playerInfo['damage']['max']) - random_int(0, $enemy->getPlayer()->getArmor()->getArmor());
        // enemy attack
        $enemyAttack = random_int((int) $enemyInfo['damage']['min'], (int) $enemyInfo['damage']['max']) - random_int(0, $player->getArmor()->getArmor());
        if ($playerAttack > 0) {
            $sessionDaily['enemy_current_life'] -= $playerAttack;
        } else {
            $playerAttack = 0;
        }

        if ($enemyAttack > 0) {
            $sessionDaily['player_current_life'] -= $enemyAttack;
        } else {
            $enemyAttack = 0;
        }

        $sessionDaily['player_life_lose'] = $enemyAttack;
        $sessionDaily['enemy_life_lose'] = $playerAttack;
        $this->em->flush();
        $this->setSessionDaily($user, $sessionDaily);
    }

    public function endFightDaily(User $user)
    {
        $player = $this->getOrCreate($user);
        $enemy = $this->getOrCreateDailyEnemy();
        $sessionDaily = $this->getSessionDaily($user);
        $result = ['mode' => null];
        if ($sessionDaily['player_current_life'] <= 0) {
            $sessionDaily = null;
            $result = ['lose_xp' => 1, 'win_xp' => 1];
            $enemy->getPlayer()->setXp($enemy->getPlayer()->getXp() + $result['win_xp']);
            $player->setXp($player->getXp() - $result['lose_xp']);
            if ($player->getXp() < 0) {
                $player->setXp(0);
            }

            $result = ['mode' => 'player_death', 'result' => $result];
            $player->setDailyBattleDefeats($player->getDailyBattleDefeats() + 1);
            $enemy = $enemy;
            $enemy->getPlayer()->setDailyBattleVictories($enemy->getPlayer()->getDailyBattleVictories() + 1);
            $dailyBattle = new DailyBattle();
            $dailyBattle->setPlayer($player);
            $dailyBattle->setStatus(DailyBattle::STATUS_NEW_LOSE);
            $this->em->persist($dailyBattle);
        } elseif ($sessionDaily['enemy_current_life'] <= 0) {
            $sessionDaily = null;
            $result = ['lose_xp' => 1, 'win_xp' => 1];
            $player->setXp($player->getXp() + $result['win_xp']);
            $enemy->getPlayer()->setXp($enemy->getPlayer()->getXp() - $result['lose_xp']);
            if ($enemy->getPlayer()->getXp() < 0) {
                $enemy = $enemy;
                $enemy->getPlayer()->setXp(0);
            }

            $result = ['mode' => 'player_win', 'result' => $result];
            $enemy->getPlayer()->setDailyBattleDefeats($enemy->getPlayer()->getDailyBattleDefeats() + 1);
            $player->setDailyBattleVictories($player->getDailyBattleVictories() + 1);
            $dailyBattle = new DailyBattle();
            $dailyBattle->setPlayer($player);
            $dailyBattle->setStatus(DailyBattle::STATUS_NEW_WIN);
            $this->em->persist($dailyBattle);
        }

        $this->em->flush();
        $this->setSessionDaily($user, $sessionDaily);

        return $result;
    }

    public function play(Request $request, #[MapQueryString] ?PaginationDTO $pagination, ?User $user = null, $display = null): array|Response
    {
        $parameters = ['user' => $user, 'state' => $request->get('state', 'main'), 'mode' => $request->get('mode'), 'display' => $display ?? 'web'];
        if (!in_array($parameters['display'], ['web', 'iphone', 'ipad', 'mac'], true)) {
            $parameters['display'] = 'web';
        }

        if ($parameters['state'] === 'login') {
            if ($parameters['mode'] === 'logout') {
                $request->getSession()->invalidate();
                $this->tokenStorage->setToken(null);
                $parameters['mode'] = null;

                return new RedirectResponse($this->router->generate('darkwood_play', $parameters));
            }

            if ($parameters['mode'] === 'login' && $request->get('_username') && $request->get('_password')) {
                $user = $this->userService->findOneByUsername($request->get('_username'));
                $token = new UsernamePasswordToken($user, 'main');
                // special case for apple validation
                if ($request->get('_username') === 'apple' && $request->get('_password') === 'apple') {
                    $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                    $this->tokenStorage->setToken($token);
                    $token->setUser($user);
                    $session = $request->getSession();
                    $lastUsernameKey = SecurityRequestAttributes::LAST_USERNAME;
                    $lastUsername = $session instanceof SessionInterface ? $session->get($lastUsernameKey) : '';
                    $csrfToken = $this->tokenManager->getToken('authenticate')->getValue();
                    $parameters['last_username'] = $lastUsername;
                    $parameters['csrf_token'] = $csrfToken;

                    return $parameters;
                }

                try {
                    $this->tokenStorage->setToken($token);
                    $parameters['mode'] = null;

                    return new RedirectResponse($this->router->generate('darkwood_play', $parameters));
                } catch (AuthenticationException) {
                    $this->tokenStorage->setToken(null);
                }
            }

            $session = $request->getSession();
            $lastUsernameKey = SecurityRequestAttributes::LAST_USERNAME;
            $lastUsername = $session instanceof SessionInterface ? $session->get($lastUsernameKey) : '';
            $csrfToken = $this->tokenManager->getToken('authenticate')->getValue();
            $parameters['last_username'] = $lastUsername;
            $parameters['csrf_token'] = $csrfToken;

            return $parameters;
        }

        if ($parameters['state'] === 'eula') {
            return $parameters;
        }

        if ($parameters['state'] === 'profile') {
            $username = $request->get('username');
            if ($username) {
                $user = $this->userService->findOneByUsername($username);
            } else {
                $token = $this->tokenStorage->getToken();
                $user = $token instanceof TokenInterface ? $token->getUser() : null;
            }

            if (!$user instanceof User) {
                throw new NotFoundHttpException('User not found !');
            }

            $parameters['user'] = $user;

            return $parameters;
        }

        if ($parameters['state'] === 'report') {
            $username = $request->get('username');
            if ($username) {
                $user = $this->userService->findOneByUsername($username);
            } else {
                $token = $this->tokenStorage->getToken();
                $user = $token instanceof TokenInterface ? $token->getUser() : null;
            }

            if (!$user instanceof User) {
                throw new NotFoundHttpException('User not found !');
            }

            $parameters['user'] = $user;
            $parameters['confirm'] = $request->get('confirm') === 'true';

            return $parameters;
        }

        if ($parameters['state'] === 'users') {
            $query = $this->userService->findActiveQuery();
            $request->query->set('sort', $pagination?->sort ?? '');
            $users = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 56);
            $parameters['users'] = $users;

            return $parameters;
        }

        if ($parameters['state'] === 'rank') {
            $query = $this->findActiveQuery($parameters['mode']);
            $request->query->set('sort', $pagination?->sort ?? '');
            $players = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 56);
            $parameters['players'] = $players;

            return $parameters;
        }

        if ($parameters['state'] === 'chat' || $parameters['state'] === 'guestbook') {
            $page = $this->pageService->findOneActiveByRefAndHost($parameters['state'], $request->getHost());
            $comment = new CommentPage();
            $comment->setUser($user);
            $comment->setPage($page);
            $form = $this->formFactory->create(CommentType::class, $comment);
            if ($page && 'POST' === $request->getMethod()) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->commentService->save($comment);
                    $this->addFlash('success', $this->translator->trans('common.comment.submited'));

                    return new RedirectResponse($this->router->generate('darkwood_play', $parameters));
                }
            }

            $query = $this->commentService->findActiveCommentByPageQuery($page);
            $request->query->set('sort', $pagination?->sort ?? '');
            $comments = $this->paginator->paginate($query, max(1, $request->query->getInt('page', 1)), 10);
            $parameters['form'] = $form->createView();
            $parameters['comments'] = $comments;

            return $parameters;
        }

        if ($user instanceof User) {
            $player = $this->getOrCreate($user);
            if ($player->getLastFight() && $parameters['state'] !== 'combat' && $parameters['mode'] !== 'combat') {
                $parameters['state'] = 'combat';
                $parameters['mode'] = 'fight_not_ended';
            } elseif ($parameters['state'] === 'combat') {
                if ($parameters['mode'] === 'combat' && $player->getLastFight()) {
                    if ($request->get('actionFight')) {
                        $this->fight($user, 'fight');
                    } elseif ($request->get('actionUsePotion')) {
                        $this->fight($user, 'potion');
                    } elseif ($request->get('actionEndFight')) {
                        $endFight = $this->endFight($user);
                        if ($endFight['mode'] === 'player_win' || $endFight['mode'] === 'player_death') {
                            $parameters['mode'] = $endFight['mode'];
                            $parameters['data']['result'] = $endFight['result'];
                        } else {
                            $parameters['mode'] = 'combat';
                        }
                    }

                    $parameters['data']['info'] = $this->getInfo($user);
                    $parameters['data']['session'] = $this->getSession($user);
                } else {
                    if ($request->get('actionEnemyNext')) {
                        $this->nextEnemy($user);
                    } elseif ($request->get('actionEnemyPrevious')) {
                        $this->previousEnemy($user);
                    } elseif ($request->get('actionBeginFight')) {
                        /** @var Enemy $defaultEnemy */
                        $defaultEnemy = $this->enemyRepository->findDefault();
                        $enemy = $player->getCurrentEnemy() ?: $defaultEnemy;
                        $enemyInfo = $this->getEnemyInfo($enemy);
                        if ((
                            $player->getMaxFight() && $enemyInfo['previous'] && $enemyInfo['previous']->getXp() > $player->getMaxFight()->getXp()
                        ) || (
                            !$player->getMaxFight() && $enemy->getId() !== $defaultEnemy->getId()
                        )) {
                            $this->addFlash('warning', $this->translator->trans('darkwood.play.label.required_enemy_alert'));
                        } else {
                            $this->setLastFight($user);
                            $request->attributes->set('mode', 'combat');

                            return $this->play($request, null, $user);
                        }
                    }

                    $parameters['data']['info'] = $this->getInfo($user);
                    $parameters['data']['currentEnemy'] = $this->getEnemyInfo($player->getCurrentEnemy() ?: $this->enemyRepository->findDefault());
                }
            } elseif ($parameters['state'] === 'daily-battle') {
                if ($parameters['mode'] === 'combat') {
                    if ($request->get('actionFight')) {
                        $this->fightDaily($user);
                    } elseif ($request->get('actionEndFight')) {
                        $endFight = $this->endFightDaily($user);
                        if ($endFight['mode'] === 'player_win' || $endFight['mode'] === 'player_death') {
                            $parameters['mode'] = $endFight['mode'];
                            $parameters['data']['result'] = $endFight['result'];
                        } else {
                            $parameters['mode'] = 'combat';
                        }
                    }

                    $parameters['data']['session'] = $this->getSessionDaily($user);
                } else {
                    if ($request->get('actionBeginFight')) {
                        $request->attributes->set('mode', 'combat');

                        return $this->play($request, null, $user);
                    }

                    $parameters['data']['dailyBattles'] = $this->getDailyBattles();
                }

                $parameters['data']['info'] = $this->getInfo($user);
                $parameters['data']['dailyEnemyInfo'] = $this->getInfo($this->getOrCreateDailyEnemy());
            } elseif ($parameters['state'] === 'info') {
                if ($request->get('actionChooseClasse')) {
                    $this->chooseClasse($user, $request->get('actionChooseClasse'));
                } elseif ($request->get('actionAddPoint')) {
                    $this->addPoint($user, $request->get('actionAddPoint'));
                }

                $parameters['data']['info'] = $this->getInfo($user);
                $parameters['data']['classes'] = $this->getClasses();
            } elseif ($parameters['state'] === 'equipment') {
                if ($request->get('actionEquipGem')) {
                    $this->equipGem($user, $request->get('actionEquipGem'));
                } elseif ($request->get('actionThrowGem')) {
                    $this->throwGem($user, $request->get('actionThrowGem'));
                }

                $parameters['data']['info'] = $this->getInfo($user);
            } elseif ($parameters['state'] === 'hostel') {
                if ($request->get('actionRegeneration')) {
                    $this->regen($user, $request->get('actionRegeneration'));
                }

                $parameters['data']['info'] = $this->getInfo($user);
                $parameters['data']['regenerations'] = $this->getRegenerations($user);
            } elseif ($parameters['state'] === 'armor') {
                if ($request->get('actionArmorNext')) {
                    $this->nextArmor($user);
                } elseif ($request->get('actionArmorPrevious')) {
                    $this->previousArmor($user);
                } elseif ($request->get('actionArmorBuy')) {
                    $this->buyArmor($user);
                } elseif ($request->get('actionArmorSell')) {
                    $this->sellArmor($user);
                }

                $parameters['data']['info'] = $this->getInfo($user);
                $parameters['data']['armor'] = $this->getArmorInfo($player->getArmor());
                $parameters['data']['currentArmor'] = $this->getArmorInfo($player->getCurrentDefaultArmor());
            } elseif ($parameters['state'] === 'potion') {
                if ($request->get('actionPotionNext')) {
                    $this->nextPotion($user);
                } elseif ($request->get('actionPotionPrevious')) {
                    $this->previousPotion($user);
                } elseif ($request->get('actionPotionBuy')) {
                    $this->buyPotion($user);
                }

                $parameters['data']['info'] = $this->getInfo($user);
                $parameters['data']['potion'] = $this->getPotionInfo($player->getPotion());
                $parameters['data']['currentPotion'] = $this->getPotionInfo($player->getCurrentDefaultPotion());
            } elseif ($parameters['state'] === 'sword') {
                if ($request->get('actionSwordNext')) {
                    $this->nextSword($user);
                } elseif ($request->get('actionSwordPrevious')) {
                    $this->previousSword($user);
                } elseif ($request->get('actionSwordBuy')) {
                    $this->buySword($user);
                } elseif ($request->get('actionSwordSell')) {
                    $this->sellSword($user);
                }

                $parameters['data']['info'] = $this->getInfo($user);
                $parameters['data']['sword'] = $this->getSwordInfo($player->getSword());
                $parameters['data']['currentSword'] = $this->getSwordInfo($player->getCurrentDefaultSword());
            } else {
                $parameters['state'] = 'main';
            }
        } else {
            $parameters['state'] = 'not-logged';
        }

        return $parameters;
    }

    public function findActiveQuery($mode = null)
    {
        return $this->playerRepository->findActiveQuery($mode);
    }

    protected function addFlash(string $type, mixed $message): void
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            throw new LogicException('You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".', 0, $e);
        }

        if (!$session instanceof FlashBagAwareSessionInterface) {
            throw new LogicException(sprintf('You cannot use the addFlash method because class "%s" doesn\'t implement "%s".', get_debug_type($session), FlashBagAwareSessionInterface::class));
        }

        $session->getFlashBag()->add($type, $message);
    }
}
