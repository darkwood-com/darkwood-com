<?php

namespace App\Services;

use App\Entity\Article;
use App\Entity\CommentPage;
use App\Repository\ArticleRepository;
use App\Services\BaseService;
use App\Entity\Comment;
use App\Entity\Page;
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
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var CommentRepository
     */

    protected $commentRepository;

    /**
     * @var CommentPageRepository
     */
    protected $commentPageRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->commentRepository = $em->getRepository(Comment::class);
        $this->commentPageRepository = $em->getRepository(CommentPage::class);
    }

    /**
     * Update a commentTranslation.
     *
     * @param Comment $comment
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
     * @param Comment $commentTranslation
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
    public function getQueryForSearch($filters = array(), $order = 'normal')
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
}
