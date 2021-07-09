<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="page_translation", indexes={@ORM\Index(name="index_search", columns={"active"})}, uniqueConstraints={
 *      @ORM\UniqueConstraint(name="locale_page_unique",columns={"locale","page_id"})
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\PageTranslationRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class PageTranslation implements \Stringable
{
    use TimestampTrait;
    /**
     * Locale.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $locale;
    /**
     * @Assert\Valid()
     * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $page;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="pages", fileNameProperty="imageName")
     */
    protected $image;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageName;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="thumbnailPages", fileNameProperty="thumbnailImageName")
     */
    protected $thumbnailImage;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $thumbnailImageName;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $imgAlt;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $imgTitle;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoDescription;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoKeywords;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $twitterCard;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $twitterSite;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $twitterTitle;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $twitterDescription;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="twitterPages", fileNameProperty="twitterImageName")
     */
    protected $twitterImage;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $twitterImageName;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $ogTitle;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $ogType;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="ogPages", fileNameProperty="ogImageName")
     */
    protected $ogImage;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ogImageName;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $ogDescription;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * Set locale.
     *
     * @param string $locale
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
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
     * @return File
     */
    public function getThumbnailImage()
    {
        return $this->thumbnailImage;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $thumbnailImage
     */
    public function setThumbnailImage(File $thumbnailImage)
    {
        $this->thumbnailImage = $thumbnailImage;
        if ($thumbnailImage) {
            // doctrine listeners event
            $this->updated = new \DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getThumbnailImageName()
    {
        return $this->thumbnailImageName;
    }

    /**
     * @param string $thumbnailImageName
     */
    public function setThumbnailImageName($thumbnailImageName)
    {
        $this->thumbnailImageName = $thumbnailImageName;
    }

    /**
     * @param mixed $imgAlt
     */
    public function setImgAlt($imgAlt)
    {
        $this->imgAlt = $imgAlt;
    }

    /**
     * @return mixed
     */
    public function getImgAlt()
    {
        return $this->imgAlt;
    }

    /**
     * @param mixed $imgTitle
     */
    public function setImgTitle($imgTitle)
    {
        $this->imgTitle = $imgTitle;
    }

    /**
     * @return mixed
     */
    public function getImgTitle()
    {
        return $this->imgTitle;
    }

    /**
     * @param mixed $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return mixed
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param mixed $seoDescription
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }

    /**
     * @return mixed
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param mixed $seoKeywords
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;
    }

    /**
     * @return mixed
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * @param mixed $twitterTitle
     */
    public function setTwitterTitle($twitterTitle)
    {
        $this->twitterTitle = $twitterTitle;
    }

    /**
     * @return mixed
     */
    public function getTwitterTitle()
    {
        return $this->twitterTitle;
    }

    /**
     * @param mixed $twitterCard
     */
    public function setTwitterCard($twitterCard)
    {
        $this->twitterCard = $twitterCard;
    }

    /**
     * @return mixed
     */
    public function getTwitterCard()
    {
        return $this->twitterCard;
    }

    /**
     * @param mixed $twitterDescription
     */
    public function setTwitterDescription($twitterDescription)
    {
        $this->twitterDescription = $twitterDescription;
    }

    /**
     * @return mixed
     */
    public function getTwitterDescription()
    {
        return $this->twitterDescription;
    }

    /**
     * @param mixed $twitterSite
     */
    public function setTwitterSite($twitterSite)
    {
        $this->twitterSite = $twitterSite;
    }

    /**
     * @return mixed
     */
    public function getTwitterSite()
    {
        return $this->twitterSite;
    }

    /**
     * @return File
     */
    public function getTwitterImage()
    {
        return $this->twitterImage;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $twitterImage
     */
    public function setTwitterImage(File $twitterImage)
    {
        $this->twitterImage = $twitterImage;
        if ($twitterImage) {
            // doctrine listeners event
            $this->updated = new \DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getTwitterImageName()
    {
        return $this->twitterImageName;
    }

    /**
     * @param string $twitterImageName
     */
    public function setTwitterImageName($twitterImageName)
    {
        $this->twitterImageName = $twitterImageName;
    }

    /**
     * @param mixed $ogTitle
     */
    public function setOgTitle($ogTitle)
    {
        $this->ogTitle = $ogTitle;
    }

    /**
     * @return mixed
     */
    public function getOgTitle()
    {
        return $this->ogTitle;
    }

    /**
     * @param mixed $ogDescription
     */
    public function setOgDescription($ogDescription)
    {
        $this->ogDescription = $ogDescription;
    }

    /**
     * @return mixed
     */
    public function getOgDescription()
    {
        return $this->ogDescription;
    }

    /**
     * @param mixed $ogType
     */
    public function setOgType($ogType)
    {
        $this->ogType = $ogType;
    }

    /**
     * @return mixed
     */
    public function getOgType()
    {
        return $this->ogType;
    }

    /**
     * @return File
     */
    public function getOgImage()
    {
        return $this->ogImage;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $ogImage
     */
    public function setOgImage($ogImage)
    {
        $this->ogImage = $ogImage;
        if ($ogImage) {
            // doctrine listeners event
            $this->updated = new \DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getOgImageName()
    {
        return $this->ogImageName;
    }

    /**
     * @param string $ogImageName
     */
    public function setOgImageName($ogImageName)
    {
        $this->ogImageName = $ogImageName;
    }

    /**
     * Set active.
     *
     * @param bool $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set page.
     *
     * @param \App\Entity\Page $page
     */
    public function setPage(Page $page = null): void
    {
        $this->page = $page;
    }

    /**
     * Get page.
     *
     * @return \App\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
}
