<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="comment")
 *
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 * @ORM\DiscriminatorColumn(name="type", type="string")
 *
 * @ORM\DiscriminatorMap({"page" = "App\Entity\CommentPage", "article" = "App\Entity\CommentArticle"})
 */
abstract class Comment
{
    use TimestampTrait;

    /**
     * @ORM\Column(type="boolean")
     */
    protected ?bool $active = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    #[Assert\NotNull(message: 'common.comment.required_user')]
    protected ?\App\Entity\User $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="comments", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    #[Assert\NotNull(message: 'common.comment.required_page')]
    protected ?\App\Entity\Page $page = null;

    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="content", type="text")
     */
    #[Assert\NotNull(message: 'common.comment.required_content')]
    private ?string $content = null;

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
     * Set user.
     */
    public function setUser(User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set page.
     */
    public function setPage(Page $page = null): void
    {
        $this->page = $page;
    }

    /**
     * Get page.
     *
     * @return \App\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set active.
     *
     * @param bool $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
}
