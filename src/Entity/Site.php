<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="site")
 *
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @Vich\Uploadable
 */
class Site implements Stringable
{
    use TimestampTrait;
    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: '2', max: '255')]
    protected ?string $name = null;

    /**
     * @Gedmo\Slug(fields={"name"}, separator="-", unique=true, updatable=false)
     *
     * @ORM\Column(length=255, unique=true)
     */
    protected $ref;

    /**
     * @ORM\Column(name="host", type="string", length=255)
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: '2', max: '255')]
    protected ?string $host = null;

    /**
     * @ORM\Column(name="position", type="integer")
     */
    protected ?int $position = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Page>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Page", mappedBy="site", cascade={"all"})
     */
    protected \Doctrine\Common\Collections\Collection $pages;

    /**
     * @ORM\Column(type="boolean")
     */
    protected ?bool $active = true;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="sites", fileNameProperty="imageName")
     */
    protected $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $imageName = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->pages = new ArrayCollection();
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

    // PAGES

    /**
     * Add pages.
     */
    public function addPage(Page $pages): void
    {
        $this->pages[] = $pages;
    }

    /**
     * Remove pages.
     */
    public function removePage(Page $pages)
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
}
