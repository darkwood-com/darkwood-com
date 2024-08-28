<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\TagTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: TagTranslationRepository::class)]
#[ORM\Table(name: 'tag_translation')]
#[ORM\Index(name: 'index_search', columns: ['title'])]
#[ORM\UniqueConstraint(name: 'locale_tag_unique', columns: ['locale', 'tag_id'])]
class TagTranslation implements Stringable
{
    use TimestampTrait;
    /**
     * Locale.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $locale;

    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'translations', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?Tag $tag = null;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $title;

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

    /**
     * Set tag.
     */
    public function setTag(?Tag $tag = null): void
    {
        $this->tag = $tag;
    }

    /**
     * Get tag.
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }
}
