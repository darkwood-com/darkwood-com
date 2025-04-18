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
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['article:read']],
    denormalizationContext: ['groups' => ['article:write']]
)]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
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
    #[Groups(['article:read', 'article:write'])]
    #[ORM\OneToMany(targetEntity: ArticleTranslation::class, mappedBy: 'article', cascade: ['persist', 'remove'])]
    protected Collection $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Tag>
     */
    #[Groups(['article:read', 'article:write'])]
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'article_tag')]
    protected Collection $tags;

    #[ApiProperty(identifier: true)]
    #[Groups(['article:read'])]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\CommentArticle>
     */
    #[Groups(['article:read'])]
    #[ORM\OneToMany(targetEntity: CommentArticle::class, mappedBy: 'article')]
    private Collection $comments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
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
     */
    public function getId(): ?int
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
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * Get one translation.
     *
     * @param string $locale Locale
     */
    public function getOneTranslation($locale = null): ArticleTranslation
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
     */
    public function getComments(): Collection
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

    public function getTags(): Collection
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
