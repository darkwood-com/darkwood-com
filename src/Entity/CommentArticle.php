<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentArticleRepository")
 */
class CommentArticle extends \App\Entity\Comment
{
    /**
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     **/
    protected $article;
    /**
     * Set article.
     *
     * @param \App\Entity\Article $article
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
