<?php

namespace App\Form\Admin;

use App\Entity\PageTranslation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 *  Form Type.
 */
class PageTranslationType extends AbstractType
{
    /**
     * Build Form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];

        $builder->add('page', PageType::class, array(
            'locale' => $locale,
        ));

        $builder->add('title',                  TextType::class);
        $builder->add('description',            TextareaType::class);
        $builder->add('image', VichImageType::class, array(
            'required' => false,
        ));
        $builder->add('thumbnailImage', VichImageType::class, array(
            'required' => false,
        ));
        $builder->add('imgAlt',                 TextType::class);
        $builder->add('imgTitle',               TextType::class);

        $builder->add('seoTitle',               TextType::class);
        $builder->add('seoDescription',         TextareaType::class);
        $builder->add('seoKeywords',            TextType::class);

        $builder->add('twitterCard', ChoiceType::class, array(
            'placeholder' => 'Choisir :',
            'choices' => array(
                'summary' => 'Summary Card',
                'summary_large_image' => 'Summary Card with large image',
                'photo' => 'Photo Card',
            ),
            'required' => false,
        ));
        $builder->add('twitterSite',            TextType::class);
        $builder->add('twitterTitle',           TextType::class);
        $builder->add('twitterDescription',     TextType::class);
        $builder->add('twitterImage', VichImageType::class, array(
            'required' => false,
        ));

        $builder->add('ogTitle',                TextType::class);
        $builder->add('ogType',                 TextType::class);
        $builder->add('ogImage', VichImageType::class, array(
            'required' => false,
        ));
        $builder->add('ogDescription',          TextType::class);

        $builder->add('content', TextareaType::class, array(
            'required' => false,
        ));
        $builder->add('active',                 CheckboxType::class, array('required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PageTranslation::class,
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
        return 'page_translation';
    }
}
