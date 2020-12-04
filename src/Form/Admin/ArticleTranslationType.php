<?php

namespace App\Form\Admin;

use App\Entity\ArticleTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
/**
 *  Form Type.
 */
class ArticleTranslationType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $builder->add('article', \App\Form\Admin\ArticleType::class, ['locale' => $locale]);
        $builder->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('image', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['required' => false]);
        $builder->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['required' => false]);
        $builder->add('active', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false]);
        $builder->add('export_locales', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false, "mapped" => false]);
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\ArticleTranslation::class, 'locale' => null]);
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'article_translation';
    }
}
