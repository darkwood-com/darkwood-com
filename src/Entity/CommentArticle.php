<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentArticleRepository")
 */
class CommentArticle extends \App\Entity\Comment
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
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
