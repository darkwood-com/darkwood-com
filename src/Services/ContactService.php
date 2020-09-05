<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\CommentPage;
use App\Repository\CommentPageRepository;
use App\Repository\CommentRepository;
use App\Services\BaseService;
use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Class ContactService
 *
 * Object manager of contactTranslation.
 */
class ContactService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->contactRepository = $em->getRepository(Contact::class);
    }

    /**
     * Update a contactTranslation.
     *
     * @param Contact $contact
     *
     * @return Contact
     */
    public function save(Contact $contact)
    {
        $contact->setUpdated(new \DateTime('now'));

        $this->em->persist($contact);
        $this->em->flush();

        return $contact;
    }

    /**
     * Remove one contactTranslation.
     *
     * @param Contact $contactTranslation
     */
    public function remove(Contact $contact)
    {
        $this->em->remove($contact);
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
        return $this->contactRepository->queryForSearch($filters, $order);
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
        return $this->contactRepository->findOneToEdit($id);
    }
}
