<?php

namespace App\Services;

use App\Entity\Tag;
use App\Entity\TagTranslation;
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
     * @var tagRepository
     */
    protected $tagRepository;

    public function __construct(
        protected EntityManagerInterface $em,
        protected CacheInterface $appCache
    ) {
        $this->tagRepository = $em->getRepository(Tag::class);
    }

    /**
     * Update a tag.
     *
     * @param string $title
     * @param string $locale
     *
     * @return Tag
     */
    public function create($title, $locale)
    {
        $tag            = new Tag();
        $tagTranslation = new TagTranslation();
        $tagTranslation->setTitle($title);
        $tagTranslation->setLocale($locale);

        $tag->addTranslation($tagTranslation);
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
}
