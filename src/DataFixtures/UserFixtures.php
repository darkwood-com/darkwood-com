<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    /**
     * AppFixtures constructor.
     */
    public function __construct(
        private \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordEncoder
    ) {
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('matyo');
        $user->setFirstName('Mathieu');
        $user->setLastName('Ledru');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin'));
        $user->setEmail('mathieu@darkwood.fr');
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        $manager->persist($user);
        $manager->flush();
    }
}
