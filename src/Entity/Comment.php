<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\CommentRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['page' => \App\Entity\CommentPage::class, 'article' => \App\Entity\CommentArticle::class])]
#[ORM\Table(name: 'comment')]
abstract class Comment
{
    use TimestampTrait;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
    protected ?bool $active = true;

    #[Assert\NotNull(message: 'common.comment.required_user')]
    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    protected ?\App\Entity\User $user = null;

    #[Assert\NotNull(message: 'common.comment.required_page')]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Page::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id')]
    protected ?\App\Entity\Page $page = null;

    #[ORM\Column(name: 'id', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Assert\NotNull(message: 'common.comment.required_content')]
    #[ORM\Column(name: 'content', type: \Doctrine\DBAL\Types\Types::TEXT)]
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
