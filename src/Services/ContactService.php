<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Class ContactService.
 *
 * Object manager of contactTranslation.
 */
class ContactService
{
    protected ContactRepository $contactRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        /** @var ContactRepository $repository */
        $repository = $em->getRepository(Contact::class);
        $this->contactRepository = $repository;
    }

    /**
     * Update a contactTranslation.
     */
    public function save(Contact $contact): Contact
    {
        $contact->setUpdated(new DateTime('now'));
        $this->em->persist($contact);
        $this->em->flush();

        return $contact;
    }

    /**
     * Remove one contactTranslation.
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
     */
    public function getQueryForSearch($filters = [], $order = 'normal'): Query
    {
        return $this->contactRepository->queryForSearch($filters, $order);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id): mixed
    {
        return $this->contactRepository->findOneToEdit($id);
    }
}
