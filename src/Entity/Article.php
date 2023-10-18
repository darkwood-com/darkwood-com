<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: \App\Repository\ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'article')]
class Article implements Stringable
{
    use TimestampTrait;

    /**
     * Translations.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\ArticleTranslation>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\ArticleTranslation::class, mappedBy: 'article', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Tag>
     */
    #[ORM\ManyToMany(targetEntity: \App\Entity\Tag::class, inversedBy: 'articles', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'article_tag')]
    protected \Doctrine\Common\Collections\Collection $tags;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\CommentArticle>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\CommentArticle::class, mappedBy: 'article')]
    private \Doctrine\Common\Collections\Collection $comments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        $articleTs = $this->getOneTranslation();

        return $articleTs ? $articleTs->getTitle() : '';
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
     * Add translations.
     */
    public function addTranslation(ArticleTranslation $translations): void
    {
        $this->translations[] = $translations;
        $translations->setArticle($this);
    }

    /**
     * Remove translations.
     */
    public function removeTranslation(ArticleTranslation $translations)
    {
        $this->translations->removeElement($translations);
        $translations->setArticle(null);
    }

    /**
     * Get translations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get one translation.
     *
     * @param string $locale Locale
     *
     * @return ArticleTranslation
     */
    public function getOneTranslation($locale = null)
    {
        /** @var ArticleTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return $this->getTranslations()->current();
    }

    /**
     * Add comments.
     */
    public function addComment(CommentArticle $comments): void
    {
        $this->comments[] = $comments;
        $comments->setArticle($this);
    }

    /**
     * Remove comments.
     */
    public function removeComment(CommentArticle $comments)
    {
        $this->comments->removeElement($comments);
        $comments->setArticle(null);
    }

    /**
     * Get comments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    // KEYWORDS

    /**
     * Add tags.
     */
    public function addTag(Tag $tag): void
    {
        $this->tags[] = $tag;
        $tag->addArticle($this);
    }

    /**
     * Remove tags.
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->removeArticle($this);
    }

    public function removeAllTags()
    {
        foreach ($this->getTags() as $tag) {
            $this->removeTag($tag);
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getAllTagTitles()
    {
        $tagTitles = [];
        foreach ($this->tags as $tag) {
            $tagTitles[] = $tag->getOneTranslation()->getTitle();
        }

        return $tagTitles;
    }
}
