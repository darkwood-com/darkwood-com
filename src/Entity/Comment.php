<?php

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"page" = "App\Entity\CommentPage"})
 */
abstract class Comment
{
    use \App\Entity\Traits\TimestampTrait;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @Assert\NotNull(message="common.comment.required_content")
     * @ORM\Column(name="content", type="text")
     */
    private $content;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = true;
    /**
     * @var User
     *
     * @Assert\NotNull(message="common.comment.required_user")
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;
    /**
     * @var Page
     *
     * @Assert\NotNull(message="common.comment.required_page")
     * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     **/
    protected $page;
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
     *
     * @param \App\Entity\User $user
     */
    public function setUser(\App\Entity\User $user = null): void
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
     *
     * @param \App\Entity\Page $page
     */
    public function setPage(\App\Entity\Page $page = null): void
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
