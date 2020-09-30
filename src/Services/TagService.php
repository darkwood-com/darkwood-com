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
