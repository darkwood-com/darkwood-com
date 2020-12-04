<?php

namespace App\Form\Admin;

use App\Entity\AppContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
/**
 *  Form Type.
 */
class AppContentType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('slug', TextType::class);
        $builder->add('title', TextType::class);
        $builder->add('content', TextareaType::class, ['required' => false]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => AppContent::class]);
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_content';
    }
}
