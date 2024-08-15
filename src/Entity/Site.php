<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\SiteRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'site')]
class Site implements Stringable
{
    use TimestampTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    protected ?string $name = null;

    /**
     * @Gedmo\Slug(fields={"name"}, separator="-", unique=true, updatable=false)
     */
    #[ORM\Column(length: 255, unique: true)]
    protected $ref;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(name: 'host', type: Types::STRING, length: 255)]
    protected ?string $host = null;

    #[ORM\Column(name: 'position', type: Types::INTEGER)]
    protected ?int $position = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'site', cascade: ['all'])]
    protected Collection $pages;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected ?bool $active = true;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'sites', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
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
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getActive(): mixed
    {
        return $this->active;
    }

    public function setActive(mixed $active)
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
     */
    public function getName(): string
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
     */
    public function getRef(): string
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
     */
    public function getHost(): string
    {
        return $this->host;
    }

    public function getPosition(): int
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
     */
    public function getPages(): Collection
    {
        return $this->pages;
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

    public function getImageName(): string
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
