<?php

namespace App\Form\Admin;

use App\Entity\PageTranslation;
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
class PageTranslationType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $builder->add('page', \App\Form\Admin\PageType::class, ['locale' => $locale]);
        $builder->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('description', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class);
        $builder->add('image', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['required' => false]);
        $builder->add('thumbnailImage', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['required' => false]);
        $builder->add('imgAlt', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('imgTitle', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('seoTitle', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('seoDescription', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class);
        $builder->add('seoKeywords', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('twitterCard', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['placeholder' => 'Choisir :', 'choices' => ['summary' => 'Summary Card', 'summary_large_image' => 'Summary Card with large image', 'photo' => 'Photo Card'], 'required' => false]);
        $builder->add('twitterSite', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('twitterTitle', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('twitterDescription', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('twitterImage', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['required' => false]);
        $builder->add('ogTitle', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('ogType', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('ogImage', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['required' => false]);
        $builder->add('ogDescription', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['required' => false]);
        $builder->add('active', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false]);
        $builder->add('export_locales', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false, "mapped" => false]);
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\PageTranslation::class, 'locale' => null]);
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
