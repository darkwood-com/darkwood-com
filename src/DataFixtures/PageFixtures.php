<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Services\SiteService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        protected ParameterBagInterface $parameterBagInterface,
        private readonly SiteService $siteService
    ) {
    }

    public function load(ObjectManager $manager)
    {
        $this->createPage(['ref' => 'home', 'title' => 'Home'], $manager);
        $this->createPage(['ref' => 'contact', 'title' => 'Contact'], $manager);
        $this->createPage(['ref' => 'sitemap', 'title' => 'Sitemap'], $manager);
        $manager->flush();
    }

    public function createPage($params, ObjectManager $manager)
    {
        $sites = $this->siteService->findAll();
        foreach ($sites as $site) {
            $page = new Page();
            $page->setRef($params['ref']);
            $page->setSite($site);
            foreach ($this->parameterBagInterface->get('app_locales') as $locale) {
                $pageTranslation = new PageTranslation();
                $pageTranslation->setTitle($params['title']);
                $pageTranslation->setLocale($locale);
                $page->addTranslation($pageTranslation);
                $manager->persist($pageTranslation);
            }

            $manager->persist($page);
        }
    }

    public function getDependencies()
    {
        return [\App\DataFixtures\SiteFixtures::class];
    }
}
