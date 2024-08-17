<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AppRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: AppRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'app')]
class App extends Page
{
    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'bannerApps', fileNameProperty: 'bannerName')]
    protected $banner;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $bannerName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $theme = null;

    /**
     * Contents.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\AppContent>
     */
    #[ORM\OneToMany(targetEntity: AppContent::class, mappedBy: 'app', cascade: ['persist', 'remove'])]
    protected Collection $contents;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    /**
     * Add content.
     */
    public function addContent(AppContent $content): void
    {
        $this->contents[] = $content;
        $content->setApp($this);
    }

    /**
     * Remove content.
     */
    public function removeContent(AppContent $content)
    {
        $this->contents->removeElement($content);
        $content->setApp(null);
    }

    /**
     * Get contents.
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function getBanner(): mixed
    {
        return $this->banner;
    }

    /**
     * @param File|UploadedFile $banner
     */
    public function setBanner(File $banner)
    {
        $this->banner = $banner;
        if ($banner) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    public function getBannerName(): ?string
    {
        return $this->bannerName;
    }

    /**
     * @param string $bannerName
     */
    public function setBannerName($bannerName)
    {
        $this->bannerName = $bannerName;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }
}
