<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserService
 *
 * Object manager of user.
 */
class UserService
{
    /**
     * Repository.
     *
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        $this->userRepository = $em->getRepository(User::class);
    }

    /**
     * Save a user.
     */
    public function save(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Remove one user.
     */
    public function remove(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function searchQuery($filters = [])
    {
        return $this->userRepository->createQueryBuilder('u')->addOrderBy('u.created', 'desc');
    }

    /**
     * Get all user.
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function getQueryForSearch($filters = [])
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
     * @param string $email
     *
     * @return mixed
     */
    public function findOneByEmail($email)
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    /**
     * @param string $username
     *
     * @return object|null
     */
    public function findOneByUsername($username)
    {
        return $this->userRepository->findOneBy(['username' => $username]);
    }

    public function findAll()
    {
        return $this->userRepository->findAll();
    }

    public function findActiveQuery()
    {
        return $this->userRepository->findActiveQuery();
    }
}
