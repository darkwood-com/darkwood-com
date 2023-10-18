<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="app")
 *
 * @ORM\Entity(repositoryClass="App\Repository\AppRepository")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @Vich\Uploadable
 */
class App extends Page
{
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="bannerApps", fileNameProperty="bannerName")
     */
    protected $banner;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $bannerName;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $theme;

    /**
     * Contents.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AppContent", mappedBy="app", cascade={"persist", "remove"})
     */
    protected $contents;

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
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @return mixed
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $banner
     */
    public function setBanner(File $banner)
    {
        $this->banner = $banner;
        if ($banner) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getBannerName()
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

    /**
     * @return string
     */
    public function getTheme()
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
