<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ContactRepository.
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     *
     * @return Query
     */
    public function queryForSearch($filters = [], $order = null)
    {
        $qb = $this->createQueryBuilder('c')->select('c');
        if ($order == 'normal') {
            $qb->addOrderBy('c.created', 'desc');
        }

        //$qb->getQuery()->useResultCache(true, 120, 'PageRepository::queryForSearch');
        $query = $qb->getQuery();

        return $query;
    }

    /**
     * Find one for edit.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        $qb = $this->createQueryBuilder('c')->select('c')->where('c.id = :id')->setParameter('id', $id);
        //$qb->getQuery()->useResultCache(true, 120, 'PageRepository::findOneToEdit'.($id ? 'id' : ''));
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }
}
