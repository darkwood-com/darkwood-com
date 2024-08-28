<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['page' => CommentPage::class, 'article' => CommentArticle::class])]
#[ORM\Table(name: 'comment')]
abstract class Comment
{
    use TimestampTrait;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected ?bool $active = true;

    #[Assert\NotNull(message: 'common.comment.required_user')]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    protected ?User $user = null;

    #[Assert\NotNull(message: 'common.comment.required_page')]
    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id')]
    protected ?Page $page = null;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Assert\NotNull(message: 'common.comment.required_content')]
    #[ORM\Column(name: 'content', type: Types::TEXT)]
    private ?string $content = null;

    /**
     * Get id.
     */
    public function getId(): ?int
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
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set user.
     */
    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Get user.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set page.
     */
    public function setPage(?Page $page = null): void
    {
        $this->page = $page;
    }

    /**
     * Get page.
     */
    public function getPage(): Page
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
     */
    public function getActive(): bool
    {
        return $this->active;
    }
}
