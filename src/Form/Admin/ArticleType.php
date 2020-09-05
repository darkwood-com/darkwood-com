<?php

namespace App\Form\Admin;

use App\Entity\Article;
use App\Form\Transformer\TagTransformer;
use App\Services\AppService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *  Form Type.
 */
class ArticleType extends AbstractType
{
    /**
     * @var TagTransformer
     */
    private $tagTransformer;

    public function __construct(
        TagTransformer $tagTransformer
    )
    {
        $this->tagTransformer = $tagTransformer;
    }

    /**
     * Build Form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];

        $this->tagTransformer->setLocale($locale);

        $builder->add(
            $builder->create('tags', TextType::class)
                ->addModelTransformer($this->tagTransformer)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Article::class,
            'locale' => null,
        ));
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
