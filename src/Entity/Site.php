<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
/**
 * @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Site implements \Stringable
{
    use \App\Entity\Traits\TimestampTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;
    /**
     * @Gedmo\Slug(fields={"name"}, separator="-", unique=true, updatable=false)
     * @ORM\Column(length=255, unique=true)
     */
    protected $ref;
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @ORM\Column(name="host", type="string", length=255)
     */
    protected $host;
    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Page", mappedBy="site", cascade={"all"})
     **/
    protected $pages;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = true;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="sites", fileNameProperty="imageName")
     */
    protected $image;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageName;
    /**
     * @var string
     *
     * @ORM\Column(name="ga_id", type="string", length=255, nullable=true)
     */
    protected $gaId;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function __toString(): string
    {
        return $this->getName();
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
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Set ref.
     *
     * @param string $ref
     */
    public function setRef($ref): void
    {
        $this->ref = $ref;
    }
    /**
     * Get ref.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }
    /**
     * Set host.
     *
     * @param string $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }
    /**
     * Get host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }
    //PAGES
    /**
     * Add pages.
     */
    public function addPage(\App\Entity\Page $pages): void
    {
        $this->pages[] = $pages;
    }
    /**
     * Remove pages.
     */
    public function removePage(\App\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
    }
    /**
     * Get pages.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages()
    {
        return $this->pages;
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
    public function setImage(\Symfony\Component\HttpFoundation\File\File $image)
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
     * Set gaId
     *
     * @param string $gaId
     */
    public function setGaId($gaId): void
    {
        $this->gaId = $gaId;
    }
    /**
     * Get gaId
     *
     * @return string
     */
    public function getGaId()
    {
        return $this->gaId;
    }
}
