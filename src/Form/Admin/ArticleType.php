<?php

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
class ArticleType extends \Symfony\Component\Form\AbstractType
{
    public function __construct(
        /**
         * @var TagTransformer
         */
        private \App\Form\Transformer\TagTransformer $tagTransformer
    )
    {
    }
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $this->tagTransformer->setLocale($locale);
        $builder->add($builder->create('tags', \Symfony\Component\Form\Extension\Core\Type\TextType::class)->addModelTransformer($this->tagTransformer));
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Article::class, 'locale' => null]);
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'article';
    }
}
