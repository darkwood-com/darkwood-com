<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="article_translation", indexes={@ORM\Index(name="index_search", columns={"active"})}, uniqueConstraints={
 *      @ORM\UniqueConstraint(name="locale_article_unique",columns={"locale","article_id"})
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\ArticleTranslationRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class ArticleTranslation implements \Stringable
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $article;

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
     * Slug.
     *
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="articles", fileNameProperty="imageName")
     */
    protected $image;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageName;

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

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
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
     *
     * @param \App\Entity\Article $article
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

    public function __toString(): string
    {
        return $this->getTitle();
    }
}
