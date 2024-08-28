<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\AppContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppContentRepository::class)]
#[ORM\Table(name: 'app_content')]
#[ORM\UniqueConstraint(name: 'locale_app_slug_unique', columns: ['locale', 'app_id', 'slug'])]
class AppContent
{
    use TimestampTrait;
    /**
     * Locale.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $locale;

    #[ORM\ManyToOne(targetEntity: App::class, inversedBy: 'contents', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'app_id', referencedColumnName: 'id', onDelete: 'cascade')]
    protected ?App $app = null;

    /**
     * Slug.
     *
     * @Gedmo\Slug(fields={"title"}, unique=false)
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $slug;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    protected string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $content = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $position = null;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * Set slug.
     *
     * @param string $slug
     */
    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    /**
     * Get slug.
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * Get content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set app.
     */
    public function setApp(?App $app = null): void
    {
        $this->app = $app;
    }

    /**
     * Get app.
     */
    public function getApp(): App
    {
        return $this->app;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }
}
