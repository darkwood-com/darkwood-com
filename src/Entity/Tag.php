<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @ORM\Table(name="tag")
 */
class Tag
{
    use TimestampTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Translations.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TagTranslation", mappedBy="tag", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Article", mappedBy="tags", cascade={"persist"})
     */
    protected $articles;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
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
     *
     * @param \App\Entity\TagTranslation $translations
     */
    public function addTranslation(\App\Entity\TagTranslation $translations): void
    {
        $this->translations[] = $translations;
        $translations->setTag($this);
    }

    /**
     * Remove translations.
     *
     * @param \App\Entity\TagTranslation $translations
     */
    public function removeTranslation(\App\Entity\TagTranslation $translations)
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
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        return $this->getTranslations()->current();
    }

    public function __toString()
    {
        $tagTs = $this->getOneTranslation();

        return $tagTs ? $tagTs->getTitle() : '';
    }

    // ARTICLES
    /**
     * Add articles.
     *
     * @param \App\Entity\Article $article
     */
    public function addArticle(\App\Entity\Article $article): void
    {
        $this->articles[] = $article;
    }

    /**
     * Remove articles.
     *
     * @param \App\Entity\Article $article
     */
    public function removeArticle(\App\Entity\Article $article)
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
