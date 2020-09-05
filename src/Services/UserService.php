<?php

namespace App\Services;

use App\Entity\Tag;
use App\Services\BaseService;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class UserService
 *
 * Object manager of user.
 */
class UserService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Repository.
     *
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
    }

    /**
     * Save a user.
     *
     * @param User $user
     */
    public function save(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Remove one user.
     *
     * @param User $user
     */
    public function remove(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function searchQuery($filters = array())
    {
        return $this->userRepository->createQueryBuilder( 'u')->addOrderBy('u.created', 'desc');
    }

    /**
     * Get all user.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function getQueryForSearch($filters = array())
    {
        return $this->userRepository->queryForSearch($filters);
    }

    /**
     * Find user by slug for edit profil.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        return $this->userRepository->findOneToEdit($id);
    }

    /**
     * Find one by email.
     *
     * @param $email
     *
     * @return mixed
     */
    public function findOneByEmail($email)
    {
        return $this->userRepository->findOneBy(array('email' => $email));
    }

    /**
     * @param $username
     *
     * @return null|object
     */
    public function findOneByUsername($username)
    {
        return $this->userRepository->findOneBy(array('username' => $username));
    }

    /**
     * Find one by email.
     *
     * @param $email
     *
     * @return mixed
     */
    public function findAll()
    {
        return $this->userRepository->findAll();
    }

    public function findActiveQuery()
    {
        return $this->userRepository->findActiveQuery();
    }
}
