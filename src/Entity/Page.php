<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['site', 'ref'])]
#[ORM\Entity(repositoryClass: \App\Repository\PageRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['page' => \App\Entity\Page::class, 'app' => \App\Entity\App::class])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'page')]
#[ORM\UniqueConstraint(name: 'ref_site_unique', columns: ['ref', 'site_id'])]
class Page implements Stringable
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * Translations.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\PageTranslation>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\PageTranslation::class, mappedBy: 'page', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $translations;

    #[Assert\NotBlank]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    protected ?string $ref = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Site::class, inversedBy: 'pages', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'site_id', referencedColumnName: 'id')]
    protected ?\App\Entity\Site $site = null;

    /**
     * Comments.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Comment>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Comment::class, mappedBy: 'page', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $comments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function __toString(): string
    {
        $pageTs = $this->getOneTranslation();

        return $pageTs ? $pageTs->getTitle() : '';
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
    public function addTranslation(PageTranslation $translations): void
    {
        $this->translations[] = $translations;
        $translations->setPage($this);
    }

    /**
     * Remove translations.
     */
    public function removeTranslation(PageTranslation $translations)
    {
        $this->translations->removeElement($translations);
        $translations->setPage(null);
    }

    /**
     * Get translations.
     *
     * @return ArrayCollection<PageTranslation>
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
     * @return PageTranslation
     */
    public function getOneTranslation($locale = null)
    {
        /** @var PageTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return $this->getTranslations()->current();
    }

    /**
     * @return mixed
     */
    public function getRef()
    {
        return $this->ref;
    }

    public function setRef(mixed $ref)
    {
        $this->ref = $ref;
    }

    // SITE

    /**
     * Set site.
     */
    public function setSite(Site $site = null): void
    {
        $this->site = $site;
    }

    /**
     * Get page.
     *
     * @return \App\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Add comment.
     */
    public function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
        $comment->setPage($this);
    }

    /**
     * Remove comment.
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
        $comment->setPage(null);
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
}
