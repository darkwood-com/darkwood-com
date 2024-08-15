<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function sprintf;

/**
 * @method null|User find($id, $lockMode = null, $lockVersion = null)
 * @method null|User findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function loadUserByIdentifier(string $usernameOrEmail): ?UserInterface
    {
        $qb = $this->createQueryBuilder('u')->select('u');
        $qb->where($qb->expr()->orX('u.username = :query', 'u.email = :query'))->setParameter('query', $usernameOrEmail);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get all user query, using for pagination.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function queryForSearch($filters = [])
    {
        $qb = $this->createQueryBuilder('u')->select('u')->orderBy('u.lastname', 'asc');
        if ($filters !== []) {
            foreach ($filters as $key => $filter) {
                $qb->andWhere('u.' . $key . ' LIKE :' . $key);
                $qb->setParameter($key, '%' . $filter . '%');
            }
        }

        return $qb->getQuery();
    }

    /**
     * Find one for edit profile.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        $qb = $this->createQueryBuilder('u')->select('u')->where('u.id = :id')->setParameter('id', $id);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findActiveQuery()
    {
        $qb = $this->createQueryBuilder('u')->select('u')->addOrderBy('u.created', 'desc');

        return $qb->getQuery();
    }
}
