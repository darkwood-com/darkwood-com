<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * @ORM\Table(name="tag_translation", indexes={@ORM\Index(name="index_search", columns={"title"})}, uniqueConstraints={
 *
 *      @ORM\UniqueConstraint(name="locale_tag_unique",columns={"locale","tag_id"})
 * }))
 *
 * @ORM\Entity(repositoryClass="App\Repository\TagTranslationRepository")
 */
class TagTranslation implements Stringable
{
    use TimestampTrait;
    /**
     * Locale.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $locale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="translations", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="cascade")
     */
    protected ?\App\Entity\Tag $tag = null;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $title;

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
     * Set tag.
     */
    public function setTag(Tag $tag = null): void
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
