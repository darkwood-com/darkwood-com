<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentArticle;
use App\Entity\CommentPage;
use App\Entity\Page;
use App\Repository\CommentArticleRepository;
use App\Repository\CommentPageRepository;
use App\Repository\CommentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
    protected CommentRepository $commentRepository;

    /**
     * @var CommentPageRepository
     */
    protected CommentPageRepository $commentPageRepository;

    /**
     * @var CommentArticleRepository
     */
    protected CommentArticleRepository $commentArticleRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        /** @var CommentRepository $repository */
        $repository = $em->getRepository(Comment::class);
        $this->commentRepository = $repository;

        /** @var CommentPageRepository $pageRepository */
        $pageRepository = $em->getRepository(CommentPage::class);
        $this->commentPageRepository = $pageRepository;

        /** @var CommentArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(CommentArticle::class);
        $this->commentArticleRepository = $articleRepository;
    }

    /**
     * Update a commentTranslation.
     */
    public function save(Comment $comment): Comment
    {
        $comment->setUpdated(new DateTime('now'));
        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    /**
     * Remove one commentTranslation.
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
     */
    public function getQueryForSearch($filters = [], $order = 'normal'): Query
    {
        return $this->commentRepository->queryForSearch($filters, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id): mixed
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
