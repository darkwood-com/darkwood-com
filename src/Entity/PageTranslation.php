<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\PageTranslationRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PageTranslationRepository::class)]
#[ORM\Table(name: 'page_translation')]
#[ORM\Index(name: 'index_search', columns: ['active'])]
#[ORM\UniqueConstraint(name: 'locale_page_unique', columns: ['locale', 'page_id'])]
class PageTranslation implements Stringable
{
    use TimestampTrait;

    /**
     * Locale.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $locale;

    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'translations', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Page $page = null;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'pages', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'thumbnailPages', fileNameProperty: 'thumbnailImageName')]
    protected $thumbnailImage;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $thumbnailImageName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $imgAlt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $imgTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $seoTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $seoDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $seoKeywords = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $twitterCard = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $twitterSite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $twitterTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $twitterDescription = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'twitterPages', fileNameProperty: 'twitterImageName')]
    protected $twitterImage;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $twitterImageName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $ogTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $ogType = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'ogPages', fileNameProperty: 'ogImageName')]
    protected $ogImage;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $ogImageName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $ogDescription = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected ?bool $active = true;

    /**
     * Constructor.
     */
    public function __construct() {}

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
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get id.
     */
    public function getId(): int
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
     */
    public function getTitle(): string
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
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function setContent(mixed $content)
    {
        $this->content = $content;
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

    public function getThumbnailImage(): File
    {
        return $this->thumbnailImage;
    }

    /**
     * @param File|UploadedFile $thumbnailImage
     */
    public function setThumbnailImage(File $thumbnailImage)
    {
        $this->thumbnailImage = $thumbnailImage;
        if ($thumbnailImage) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    public function getThumbnailImageName(): string
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

    public function getImgAlt(): mixed
    {
        return $this->imgAlt;
    }

    public function setImgTitle(mixed $imgTitle)
    {
        $this->imgTitle = $imgTitle;
    }

    public function getImgTitle(): mixed
    {
        return $this->imgTitle;
    }

    public function setSeoTitle(mixed $seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    public function getSeoTitle(): mixed
    {
        return $this->seoTitle;
    }

    public function setSeoDescription(mixed $seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }

    public function getSeoDescription(): mixed
    {
        return $this->seoDescription;
    }

    public function setSeoKeywords(mixed $seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;
    }

    public function getSeoKeywords(): mixed
    {
        return $this->seoKeywords;
    }

    public function setTwitterTitle(mixed $twitterTitle)
    {
        $this->twitterTitle = $twitterTitle;
    }

    public function getTwitterTitle(): mixed
    {
        return $this->twitterTitle;
    }

    public function setTwitterCard(mixed $twitterCard)
    {
        $this->twitterCard = $twitterCard;
    }

    public function getTwitterCard(): mixed
    {
        return $this->twitterCard;
    }

    public function setTwitterDescription(mixed $twitterDescription)
    {
        $this->twitterDescription = $twitterDescription;
    }

    public function getTwitterDescription(): mixed
    {
        return $this->twitterDescription;
    }

    public function setTwitterSite(mixed $twitterSite)
    {
        $this->twitterSite = $twitterSite;
    }

    public function getTwitterSite(): mixed
    {
        return $this->twitterSite;
    }

    public function getTwitterImage(): File
    {
        return $this->twitterImage;
    }

    /**
     * @param File|UploadedFile $twitterImage
     */
    public function setTwitterImage(File $twitterImage)
    {
        $this->twitterImage = $twitterImage;
        if ($twitterImage) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    public function getTwitterImageName(): string
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

    public function getOgTitle(): mixed
    {
        return $this->ogTitle;
    }

    public function setOgDescription(mixed $ogDescription)
    {
        $this->ogDescription = $ogDescription;
    }

    public function getOgDescription(): mixed
    {
        return $this->ogDescription;
    }

    public function setOgType(mixed $ogType)
    {
        $this->ogType = $ogType;
    }

    public function getOgType(): mixed
    {
        return $this->ogType;
    }

    public function getOgImage(): File
    {
        return $this->ogImage;
    }

    /**
     * @param File|UploadedFile $ogImage
     */
    public function setOgImage($ogImage)
    {
        $this->ogImage = $ogImage;
        if ($ogImage) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    public function getOgImageName(): string
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
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set page.
     */
    public function setPage(?Page $page = null): void
    {
        $this->page = $page;
    }

    /**
     * Get page.
     */
    public function getPage(): Page
    {
        return $this->page;
    }
}
