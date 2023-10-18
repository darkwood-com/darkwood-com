<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: \App\Repository\TagRepository::class)]
#[ORM\Table(name: 'tag')]
class Tag implements Stringable
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * Translations.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\TagTranslation>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\TagTranslation::class, mappedBy: 'tag', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Article>
     */
    #[ORM\ManyToMany(targetEntity: \App\Entity\Article::class, mappedBy: 'tags', cascade: ['persist'])]
    protected \Doctrine\Common\Collections\Collection $articles;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        $tagTs = $this->getOneTranslation();

        return $tagTs ? $tagTs->getTitle() : '';
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
    public function addTranslation(TagTranslation $translations): void
    {
        $this->translations[] = $translations;
        $translations->setTag($this);
    }

    /**
     * Remove translations.
     */
    public function removeTranslation(TagTranslation $translations)
    {
        $this->translations->removeElement($translations);
        $translations->setTag(null);
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
     * @return TagTranslation
     */
    public function getOneTranslation($locale = null)
    {
        /** @var TagTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return $this->getTranslations()->current();
    }

    // ARTICLES

    /**
     * Add articles.
     */
    public function addArticle(Article $article): void
    {
        $this->articles[] = $article;
    }

    /**
     * Remove articles.
     */
    public function removeArticle(Article $article)
    {
        $this->articles->removeElement($article);
    }

    public function removeAllArticles()
    {
        foreach ($this->getArticles() as $article) {
            $this->removeArticle($article);
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }
}
