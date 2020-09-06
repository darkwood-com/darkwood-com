<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tag_translation", indexes={@ORM\Index(name="index_search", columns={"title"})}, uniqueConstraints={
 *      @ORM\UniqueConstraint(name="locale_tag_unique",columns={"locale","tag_id"})
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\TagTranslationRepository")
 */
class TagTranslation
{
    use TimestampTrait;

    /**
     * Locale.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $locale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $tag;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    public function __toString()
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
     * Set tag.
     *
     * @param \App\Entity\Tag $tag
     */
    public function setTag(\App\Entity\Tag $tag = null): void
    {
        $this->tag = $tag;
    }

    /**
     * Get tag.
     *
     * @return \App\Entity\Tag
     */
    public function getTag()
    {
        return $this->tag;
    }
}
