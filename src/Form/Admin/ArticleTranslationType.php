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
class ArticleTranslationType extends AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];

        $builder->add('article', ArticleType::class, [
            'locale' => $locale,
        ]);

        $builder->add('title', TextType::class);
        $builder->add('image', VichImageType::class, [
            'required' => false,
        ]);
        $builder->add('content', TextareaType::class, [
            'required' => false,
        ]);

        $builder->add('active', CheckboxType::class, ['required' => false]);
        $builder->add('export_locales', CheckboxType::class, ['required' => false, "mapped" => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArticleTranslation::class,
            'locale'     => null,
        ]);
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
