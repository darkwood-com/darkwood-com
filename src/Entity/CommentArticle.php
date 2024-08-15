<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommentArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentArticleRepository::class)]
class CommentArticle extends Comment
{
    #[Assert\NotNull(message: 'common.comment.required_page')]
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id')]
    protected ?Article $article = null;

    /**
     * Set article.
     */
    public function setArticle(?Article $article = null): void
    {
        $this->article = $article;
    }

    /**
     * Get article.
     */
    public function getArticle(): Article
    {
        return $this->article;
    }
}
