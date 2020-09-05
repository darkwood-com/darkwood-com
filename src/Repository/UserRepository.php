<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function queryForSearch($filters = array())
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.lastname', 'asc');

        if (count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                $qb->andWhere('u.'.$key.' LIKE :'.$key);
                $qb->setParameter($key, '%'.$filter.'%');
            }
        }

        return $qb->getQuery();
    }

    /**
     * Find one for edit profile.
     *
     * @param $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.id = :id')
            ->setParameter('id', $id);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findActiveQuery()
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->addOrderBy('u.created', 'desc')
        ;

        return $qb->getQuery();
    }
}
