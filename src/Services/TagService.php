<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class TagService.
 *
 * Object manager of tags.
 */
class TagService
{
    /**
     * @var TagRepository
     */
    protected TagRepository $tagRepository;

    public function __construct(
        protected EntityManagerInterface $em,
        protected CacheInterface $appCache
    ) {
        /** @var TagRepository $repository */
        $repository = $em->getRepository(Tag::class);
        $this->tagRepository = $repository;
    }

    /**
     * Update a tag.
     *
     * @param string $title
     * @param string $locale
     */
    public function create($title, $locale): Tag
    {
        $tag = new Tag();
        $tagTranslation = new TagTranslation();
        $tagTranslation->setTitle($title);
        $tagTranslation->setLocale($locale);

        $tag->addTranslation($tagTranslation);
        $this->em->persist($tag);

        return $tag;
    }

    /**
     * Update a tag.
     */
    public function save(Tag $tag): Tag
    {
        $tag->setUpdated(new DateTime('now'));
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
     * Find one to edit.
     *
     * @param string $id
     */
    public function findOneToEdit($id): mixed
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
}
