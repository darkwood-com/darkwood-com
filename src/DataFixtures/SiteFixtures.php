<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->createSite([
            'name' => 'Darkwood',
            'host' => 'darkwood.localhost',
            'position' => 1,
        ], $manager);

        $this->createSite([
            'name' => 'Apps',
            'host' => 'apps.darkwood.localhost',
            'position' => 2,
        ], $manager);

        $this->createSite([
            'name' => 'Photos',
            'host' => 'photos.darkwood.localhost',
            'position' => 3,
        ], $manager);

        $this->createSite([
            'name' => 'Blog',
            'host' => 'blog.darkwood.localhost',
            'position' => 4,
        ], $manager);

        $this->createSite([
            'name' => 'Hello',
            'host' => 'hello.darkwood.localhost',
            'position' => 5,
        ], $manager);

        $this->createSite([
            'name' => 'Api',
            'host' => 'api.darkwood.localhost',
            'position' => 6,
        ], $manager);

        $manager->flush();
    }

    public function createSite($params, ObjectManager $manager)
    {
        $site = new Site();
        $site->setName($params['name']);
        $site->setHost($params['host']);
        $site->setPosition($params['position']);
        $site->setActive(true);

        $this->addReference('site-' . $params['name'], $site);
        $manager->persist($site);

        return $site;
    }
}
