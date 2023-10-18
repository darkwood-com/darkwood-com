<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="game_classe")
 *
 * @ORM\Entity(repositoryClass="App\Repository\Game\ClasseRepository")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @Vich\Uploadable
 */
class Classe
{
    use TimestampTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="classes", fileNameProperty="imageName")
     */
    protected $image;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageName;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="classe", cascade={"persist", "remove"})
     */
    protected $players;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
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
     * @return int
     */
    public function getStrength()
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

    /**
     * @return int
     */
    public function getDexterity()
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

    /**
     * @return int
     */
    public function getVitality()
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

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImage(File $image)
    {
        $this->image = $image;
        if ($image) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getImageName()
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
     *
     * @return string
     */
    public function getTitle()
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
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }
}
