<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['site', 'ref'])]
#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['page' => Page::class, 'app' => App::class])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'page')]
#[ORM\UniqueConstraint(name: 'ref_site_unique', columns: ['ref', 'site_id'])]
class Page implements Stringable
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * Translations.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\PageTranslation>
     */
    #[ORM\OneToMany(targetEntity: PageTranslation::class, mappedBy: 'page', cascade: ['persist', 'remove'])]
    protected Collection $translations;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $ref = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'pages', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'site_id', referencedColumnName: 'id')]
    protected ?Site $site = null;

    /**
     * Comments.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'page', cascade: ['persist', 'remove'])]
    protected Collection $comments;

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
     */
    public function getId(): ?int
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
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * Get one translation.
     *
     * @param string $locale Locale
     */
    public function getOneTranslation($locale = null): PageTranslation
    {
        /** @var PageTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return $this->getTranslations()->current();
    }

    public function getRef(): mixed
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
    public function setSite(?Site $site = null): void
    {
        $this->site = $site;
    }

    /**
     * Get page.
     */
    public function getSite(): ?Site
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
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
}
