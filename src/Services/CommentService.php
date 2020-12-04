<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\CommentPage;
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
     * @var CommentRepository
     */
    protected $commentRepository;
    /**
     * @var CommentPageRepository
     */
    protected $commentPageRepository;
    public function __construct(
        /**
         * @var EntityManagerInterface
         */
        protected \Doctrine\ORM\EntityManagerInterface $em
    )
    {
        $this->commentRepository = $em->getRepository(\App\Entity\Comment::class);
        $this->commentPageRepository = $em->getRepository(\App\Entity\CommentPage::class);
    }
    /**
     * Update a commentTranslation.
     *
     * @return Comment
     */
    public function save(\App\Entity\Comment $comment)
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
    public function remove(\App\Entity\Comment $comment)
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
    public function findActiveCommentByPageQuery(\App\Entity\Page $page)
    {
        return $this->commentPageRepository->findActiveCommentByPageQuery($page);
    }
}
