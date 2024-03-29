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
    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        $this->contactRepository = $em->getRepository(Contact::class);
    }

    /**
     * Update a contactTranslation.
     *
     * @return Contact
     */
    public function save(Contact $contact)
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
     *
     * @return Query
     */
    public function getQueryForSearch($filters = [], $order = 'normal')
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
