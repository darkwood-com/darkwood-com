<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: \App\Repository\PageTranslationRepository::class)]
#[ORM\Table(name: 'page_translation')]
#[ORM\Index(name: 'index_search', columns: ['active'])]
#[ORM\UniqueConstraint(name: 'locale_page_unique', columns: ['locale', 'page_id'])]
class PageTranslation implements Stringable
{
    use TimestampTrait;
    /**
     * Locale.
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: false)]
    protected string $locale;

    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Page::class, inversedBy: 'translations', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Page $page = null;

    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: '2', max: '255')]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: false)]
    protected string $title;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $content = null;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="pages", fileNameProperty="imageName")
     */
    protected $image;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="thumbnailPages", fileNameProperty="thumbnailImageName")
     */
    protected $thumbnailImage;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $thumbnailImageName = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $imgAlt = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $imgTitle = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $seoTitle = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $seoDescription = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $seoKeywords = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $twitterCard = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $twitterSite = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $twitterTitle = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $twitterDescription = null;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="twitterPages", fileNameProperty="twitterImageName")
     */
    protected $twitterImage;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $twitterImageName = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $ogTitle = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $ogType = null;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="ogPages", fileNameProperty="ogImageName")
     */
    protected $ogImage;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $ogImageName = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $ogDescription = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    protected ?bool $active = true;

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

    public function setContent(mixed $content)
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
            $this->updated = new DateTime('now');
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

    public function setImgAlt(mixed $imgAlt)
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

    public function setImgTitle(mixed $imgTitle)
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

    public function setSeoTitle(mixed $seoTitle)
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

    public function setSeoDescription(mixed $seoDescription)
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

    public function setSeoKeywords(mixed $seoKeywords)
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

    public function setTwitterTitle(mixed $twitterTitle)
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

    public function setTwitterCard(mixed $twitterCard)
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

    public function setTwitterDescription(mixed $twitterDescription)
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

    public function setTwitterSite(mixed $twitterSite)
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
            $this->updated = new DateTime('now');
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

    public function setOgTitle(mixed $ogTitle)
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

    public function setOgDescription(mixed $ogDescription)
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

    public function setOgType(mixed $ogType)
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
            $this->updated = new DateTime('now');
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
