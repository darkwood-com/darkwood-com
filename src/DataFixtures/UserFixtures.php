<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends \Doctrine\Bundle\FixturesBundle\Fixture
{
    /**
     * AppFixtures constructor.
     */
    public function __construct(
        private \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
    )
    {
    }
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('matyo');
        $user->setFirstName('Mathieu');
        $user->setLastName('Ledru');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin'));
        $user->setEmail('matyo@darkwood.fr');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $manager->persist($user);
        $manager->flush();
    }
}
