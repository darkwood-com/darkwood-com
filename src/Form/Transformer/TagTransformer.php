<?php

declare(strict_types=1);

namespace App\Form\Transformer;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Services\TagService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class TagTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $locale;

    public function __construct(
        /**
         * @var TagService
         */
        private readonly TagService $tagService
    ) {}

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param mixed $tags
     *
     * @return mixed|string
     */
    public function transform($tags): mixed
    {
        $arrayTags = [];
        if (!$tags) {
            return implode(', ', $arrayTags);
        }

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $arrayTags[] = $tag->getOneTranslation($this->locale)->getTitle();
        }

        return implode(', ', $arrayTags);
    }

    /**
     * Transforms the value the users has typed to a value that suits the field in the Document.
     */
    public function reverseTransform($tags): mixed
    {
        $tagLinked = new ArrayCollection();
        if (!$tags) {
            $tags = '';
        }

        $arrayTags = array_filter(array_map('trim', explode(',', (string) $tags)));
        foreach ($arrayTags as $tag) {
            $tagPersisited = $this->tagService->findOneByTitle($tag);
            if (!$tagPersisited) {
                $newTag = new Tag();
                $newTagTranslation = new TagTranslation();
                $newTagTranslation->setTitle($tag);
                $newTagTranslation->setLocale($this->locale);
                $newTag->addTranslation($newTagTranslation);
                $this->tagService->save($newTag);
                $tagLinked->add($newTag);
            } else {
                $tagLinked->add($tagPersisited);
            }
        }

        return $tagLinked;
    }
}
