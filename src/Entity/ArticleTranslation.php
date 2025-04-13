<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Traits\TimestampTrait;
use App\Repository\ArticleTranslationRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['article_translation:read']],
    denormalizationContext: ['groups' => ['article_translation:write']]
)]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ArticleTranslationRepository::class)]
#[ORM\Table(name: 'article_translation')]
#[ORM\Index(name: 'index_search', columns: ['active'])]
#[ORM\UniqueConstraint(name: 'locale_article_unique', columns: ['locale', 'article_id'])]
class ArticleTranslation implements Stringable
{
    use TimestampTrait;
    /**
     * Locale.
     */
    #[Groups(['article_translation:read', 'article_translation:write', 'article:read'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $locale;

    #[Groups(['article_translation:read', 'article_translation:write'])]
    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'translations', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Article $article = null;

    #[ApiProperty(identifier: true)]
    #[Groups(['article_translation:read'])]
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Groups(['article_translation:read', 'article_translation:write', 'article:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $title;

    /**
     * Slug.
     */
    #[Groups(['article_translation:read', 'article_translation:write', 'article:read'])]
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $slug;

    #[Groups(['article_translation:read', 'article_translation:write', 'article:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'articles', fileNameProperty: 'imageName')]
    protected $image;

    #[Groups(['article_translation:read', 'article_translation:write'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    #[Groups(['article_translation:read', 'article_translation:write'])]
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
    public function getId(): ?int
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

    public function getSlug(): mixed
    {
        return $this->slug;
    }

    public function setSlug(mixed $slug)
    {
        $this->slug = $slug;
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

    public function getImageName(): ?string
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
     * Set article.
     */
    public function setArticle(?Article $article = null): void
    {
        $this->article = $article;
    }

    /**
     * Get article.
     */
    public function getArticle(): Article
    {
        return $this->article;
    }
}
