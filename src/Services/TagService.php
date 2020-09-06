<?php

namespace App\Services;

use App\Entity\Page;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class TagService
 *
 * Object manager of tags.
 */
class TagService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var tagRepository
     */
    protected $tagRepository;

    /**
     * @var CacheInterface
     */
    protected $appCache;

    public function __construct(
        EntityManagerInterface $em,
        CacheInterface $appCache
    ) {
        $this->em            = $em;
        $this->tagRepository = $em->getRepository(Tag::class);
        $this->appCache      = $appCache;
    }

    /**
     * Update a tag.
     *
     * @param Tag $tag
     *
     * @return Tag
     */
    public function create($title, $locale)
    {
        $tag = new Tag();
        $tag->setTitle($title);
        $tag->setLocale($locale);
        $this->em->persist($tag);

        return $tag;
    }

    /**
     * Update a tag.
     *
     * @return Tag
     */
    public function save(Tag $tag)
    {
        $tag->setUpdated(new \DateTime('now'));
        $this->em->persist($tag);
        $this->em->flush();

        return $tag;
    }

    /**
     * Remove one tag.
     */
    public function remove(Tag $tag)
    {
        $this->em->remove($tag);
        $this->em->flush();
    }

    /**
     * Update page tags.
     *
     * @param array $tags
     */
    public function updatePage($tags, Page $page)
    {
        $exists    = 0;
        $arrayTags = explode(',', $tags);
        $allTags   = $this->findAll();

        foreach ($arrayTags as $tag) {
            $exists = 0;
            /** @var Tag $persisted */
            foreach ($allTags as $persisted) {
                if ($persisted->getTitle() == $tag) {
                    $exists = 1;
                }
            }
            if ($exists == 1) {
                $page->addTag($persisted);
            } else {
                $newTag = new Tag();
                $newTag->setTitle($tag);
                $page->addTag($newTag);
                $this->save($newTag);
            }
        }
    }

    public function findCachedById($id)
    {
        $ttl = $this->cacheService->data('tag');

        return $this->tagRepository->findCachedById($id, $ttl);
    }

    /**
     * Find one to edit.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function findOneToEdit($id)
    {
        return $this->tagRepository->findOneToEdit($id);
    }

    /**
     * Find all.
     */
    public function findAll()
    {
        return $this->tagRepository->findAll();
    }

    /**
     * Find all.
     */
    public function findAllAsArray($locale = null)
    {
        return $this->tagRepository->findAllAsArray($locale);
    }

    /**
     * Find all.
     */
    public function findOneByTitle($title, $locale = null)
    {
        return $this->tagRepository->findOneByTitle($title, $locale);
    }

    public function parseFile($id)
    {
        $filename = $this->container->get('kernel')->getRootDir() . '/../web/import/tags.csv';

        if (!file_exists($filename) && !filesize($filename) > 0) {
            $error = "Fichier '" . $filename . "' vide ou inexistant";

            return $error;
        }

        /** @var Locale $locale */
        $locale = $this->container->get('by.locale')->findOneToEdit($id);
        if (!$locale) {
            $error = 'Locale inconnu...';

            return $error;
        }

        try {
            $handle = fopen($filename, 'r');
        } catch (\Exception $e) {
            $error = "Erreur lors de l'ouverture du fichier: " . $filename . '(' . $e->getMessage() . ')';

            return $error;
        }

        try {
            $cpt     = 1;
            $newTags = 0;

            while (!feof($handle)) {
                $line = fgets($handle);

                $title = str_replace("\n", '', $line);
                $title = str_replace("\r", '', $title);

                $tagPersisted = $this->findOneByTitle($title, $locale->getId());
                if (!$tagPersisted) {
                    $this->create($title, $locale);
                    ++$newTags;
                }

                ++$cpt;

                if (($cpt % 100) == 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
            $this->em->flush();
            $this->em->clear();

            if ($newTags == 0) {
                $success = 'Le fichier a été parsé avec succès, aucun nouveau tag n\'a été enregistré !';
            } else {
                $success = 'Le fichier a été parsé avec succès, ' . $newTags . ' nouveaux tags enregistrés !';
            }

            return $success;
        } catch (\Exception $e) {
            $error = 'Erreur lors du parsing du fichier (' . $e->getMessage() . ')';

            return $error;
        }
    }

    /**
     * Find titles.
     *
     * @param $slug
     * @param $max
     *
     * @return mixed
     */
    public function findByPageBreed($locale, $slug, $max)
    {
        return $this->tagRepository->findByPageBreed($locale, $slug, $max);
    }
}
