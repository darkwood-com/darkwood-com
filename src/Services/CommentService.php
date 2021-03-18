<?php

namespace App\Services;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentArticle;
use App\Entity\CommentPage;
use App\Entity\Page;
use App\Repository\CommentArticleRepository;
use App\Repository\CommentPageRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
/**
 * Class CommentService.
 *
 * Object manager of commentTranslation.
 */
class CommentService
{
    /**
     * @var CommentRepository
     */
    protected $commentRepository;

    /**
     * @var CommentPageRepository
     */
    protected $commentPageRepository;

    /**
     * @var CommentArticleRepository
     */
    protected $commentArticleRepository;

    public function __construct(
        protected EntityManagerInterface $em
    )
    {
        $this->commentRepository = $em->getRepository(Comment::class);
        $this->commentPageRepository = $em->getRepository(CommentPage::class);
        $this->commentArticleRepository = $em->getRepository(CommentArticle::class);
    }
    /**
     * Update a commentTranslation.
     *
     * @return Comment
     */
    public function save(Comment $comment)
    {
        $comment->setUpdated(new \DateTime('now'));
        $this->em->persist($comment);
        $this->em->flush();
        return $comment;
    }
    /**
     * Remove one commentTranslation.
     *
     * @param Comment $comment
     */
    public function remove(Comment $comment)
    {
        $this->em->remove($comment);
        $this->em->flush();
    }
    /**
     * Search.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function getQueryForSearch($filters = [], $order = 'normal')
    {
        return $this->commentRepository->queryForSearch($filters, $order);
    }
    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        return $this->commentRepository->findOneToEdit($id);
    }
    public function findActiveCommentByPageQuery(Page $page)
    {
        return $this->commentPageRepository->findActiveCommentByPageQuery($page);
    }
    public function findActiveCommentByArticleQuery(Article $article)
    {
        return $this->commentArticleRepository->findActiveCommentByArticleQuery($article);
    }
}
