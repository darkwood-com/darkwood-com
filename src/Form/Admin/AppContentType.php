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
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('slug', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['required' => false]);
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\AppContent::class]);
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
