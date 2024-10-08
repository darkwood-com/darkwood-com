<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Article;
use App\Form\Transformer\TagTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *  Form Type.
 */
class ArticleType extends AbstractType
{
    public function __construct(
        /**
         * @var TagTransformer
         */
        private readonly TagTransformer $tagTransformer
    ) {}

    /**
     * Build Form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'];
        $this->tagTransformer->setLocale($locale);
        $builder->add($builder->create('tags', TextType::class)->addModelTransformer($this->tagTransformer));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Article::class, 'locale' => null]);
    }
}
