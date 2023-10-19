<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\CommentArticleRepository::class)]
class CommentArticle extends \App\Entity\Comment
{
	#[Assert\NotNull(message: 'common.comment.required_page')]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Article::class, inversedBy: 'comments', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id')]
    protected ?\App\Entity\Article $article = null;

    /**
     * Set article.
     */
    public function setArticle(Article $article = null): void
    {
        $this->article = $article;
    }

    /**
     * Get article.
     *
     * @return \App\Entity\Article
     */
    public function getArticle()
    {
        return $this->article;
    }
}
