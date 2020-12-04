<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AppContent.
 *
 * @ORM\Entity(repositoryClass="App\Repository\AppContentRepository")
 * @ORM\Table(name="app_content", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="locale_app_slug_unique",columns={"locale","app_id","slug"})
 * }))
 * @ORM\HasLifecycleCallbacks
 */
class AppContent
{
    use \App\Entity\Traits\TimestampTrait;
    /**
     * Locale.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $locale;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\App", inversedBy="contents", cascade={"persist"})
     * @ORM\JoinColumn(name="app_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $app;
    /**
     * Slug.
     *
     * @Assert\NotBlank()
     * @Gedmo\Slug(fields={"title"}, unique=false)
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $slug;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;
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
     *
     * @return string
     */
    public function getSlug()
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
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * Set app.
     *
     * @param \App\Entity\App $app
     */
    public function setApp(\App\Entity\App $app = null): void
    {
        $this->app = $app;
    }
    /**
     * Get app.
     *
     * @return \App\Entity\App
     */
    public function getApp()
    {
        return $this->app;
    }
    /**
     * @return int
     */
    public function getPosition()
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
