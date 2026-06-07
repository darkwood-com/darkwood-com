<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\TimestampTrait;
use App\Enum\ArticleReactionEmoji;
use App\Repository\ArticleReactionRepository;
use App\State\ArticleReactionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/article_reactions',
            processor: ArticleReactionProcessor::class,
            read: false,
            security: "is_granted('ROLE_USER')",
            name: 'api_article_reactions_create',
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ArticleReactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'article_reaction')]
#[ORM\UniqueConstraint(name: 'article_reaction_unique', columns: ['article_id', 'user_id', 'emoji'])]
class ArticleReaction
{
    use TimestampTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Article $article;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 16, enumType: ArticleReactionEmoji::class)]
    private ArticleReactionEmoji $emoji;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEmoji(): ArticleReactionEmoji
    {
        return $this->emoji;
    }

    public function setEmoji(ArticleReactionEmoji $emoji): self
    {
        $this->emoji = $emoji;

        return $this;
    }
}
