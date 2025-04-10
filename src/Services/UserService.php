<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserService.
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
    protected UserRepository $userRepository;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        /** @var UserRepository $repository */
        $repository = $em->getRepository(User::class);
        $this->userRepository = $repository;
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
     */
    public function getQueryForSearch($filters = []): mixed
    {
        return $this->userRepository->queryForSearch($filters);
    }

    /**
     * Find user by slug for edit profil.
     *
     * @param string $id
     */
    public function findOneToEdit($id): mixed
    {
        return $this->userRepository->findOneToEdit($id);
    }

    /**
     * Find one by email.
     *
     * @param string $email
     */
    public function findOneByEmail($email): mixed
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    /**
     * @param string $username
     */
    public function findOneByUsername($username): ?object
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
