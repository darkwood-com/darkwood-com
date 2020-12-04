<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="page",uniqueConstraints={
 *      @ORM\UniqueConstraint(name="ref_site_unique",columns={"ref","site_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 * @UniqueEntity(fields={"site", "ref"})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"page" = "App\Entity\Page", "app" = "App\Entity\App"})
 * @ORM\HasLifecycleCallbacks
 */
class Page implements \Stringable
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
     * @ORM\OneToMany(targetEntity="App\Entity\PageTranslation", mappedBy="page", cascade={"persist", "remove"})
     */
    protected $translations;
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     */
    protected $ref;
    /**
     * @var Site
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="pages", cascade={"persist"})
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     **/
    protected $site;
    /**
     * Comments.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="page", cascade={"persist", "remove"})
     */
    protected $comments;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
            if ($translation->getLocale() == $locale) {
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
    /**
     * @param mixed $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }
    public function __toString(): string
    {
        $pageTs = $this->getOneTranslation();
        return $pageTs ? $pageTs->getTitle() : '';
    }
    /* SITE */
    /**
     * Set site.
     *
     * @param \App\Entity\Site $site
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
