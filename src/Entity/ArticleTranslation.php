<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: \App\Repository\ArticleTranslationRepository::class)]
#[ORM\Table(name: 'article_translation')]
#[ORM\Index(name: 'index_search', columns: ['active'])]
#[ORM\UniqueConstraint(name: 'locale_article_unique', columns: ['locale', 'article_id'])]
class ArticleTranslation implements Stringable
{
    use TimestampTrait;
    /**
     * Locale.
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: false)]
    protected string $locale;

    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Article::class, inversedBy: 'translations', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?\App\Entity\Article $article = null;

    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: false)]
    protected string $title;

    /**
     * Slug.
     *
     * @Gedmo\Slug(fields={"title"})
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: false)]
    protected string $slug;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $content = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'articles', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

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
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug(mixed $slug)
    {
        $this->slug = $slug;
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
     * Set article.
     */
    public function setArticle(Article $article = null): void
    {
        $this->article = $article;
    }

    /**
     * Get article.
     *
     * @return \App\Entity\Article
     */
    public function getArticle()
    {
        return $this->article;
    }
}
