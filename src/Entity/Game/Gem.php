<?php

namespace App\Entity\Game;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Gem.
 *
 * @ORM\Table(name="game_gem")
 * @ORM\Entity(repositoryClass="App\Repository\Game\GemRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Gem
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
     * @ORM\Column(name="power", type="integer")
     */
    private $power;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="gems", fileNameProperty="imageName")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="equipment1", cascade={"persist", "remove"})
     */
    protected $equipment1Players;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="equipment2", cascade={"persist", "remove"})
     */
    protected $equipment2Players;

    /**
     * Players.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Game\Player", mappedBy="equipment3", cascade={"persist", "remove"})
     */
    protected $equipment3Players;

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
     *
     * @return int
     */
    public function getId()
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
     *
     * @return int
     */
    public function getPower()
    {
        return $this->power;
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
            $this->updated = new \DateTime('now');
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
     * Add equipment1Player.
     */
    public function addEquipment1Player(\App\Entity\Game\Player $equipment1Player): void
    {
        $this->equipment1Players[] = $equipment1Player;
    }

    /**
     * Remove equipment1Player.
     */
    public function removeEquipment1Player(\App\Entity\Game\Player $equipment1Player)
    {
        $this->equipment1Players->removeElement($equipment1Player);
    }

    /**
     * Get equipment1Players.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipment1Players()
    {
        return $this->equipment1Players;
    }

    /**
     * Add equipment2Player.
     */
    public function addEquipment2Player(\App\Entity\Game\Player $equipment2Player): void
    {
        $this->equipment2Players[] = $equipment2Player;
    }

    /**
     * Remove equipment2Player.
     */
    public function removeEquipment2Player(\App\Entity\Game\Player $equipment2Player)
    {
        $this->equipment2Players->removeElement($equipment2Player);
    }

    /**
     * Get equipment2Players.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipment2Players()
    {
        return $this->equipment2Players;
    }

    /**
     * Add equipment3Player.
     */
    public function addEquipment3Player(\App\Entity\Game\Player $equipment3Player): void
    {
        $this->equipment3Players[] = $equipment3Player;
    }

    /**
     * Remove equipment3Player.
     */
    public function removeEquipment3Player(\App\Entity\Game\Player $equipment3Player)
    {
        $this->equipment3Players->removeElement($equipment3Player);
    }

    /**
     * Get equipment3Players.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipment3Players()
    {
        return $this->equipment3Players;
    }
}
