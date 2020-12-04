<?php

namespace App\Form\Admin;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
/**
 *  Form Type.
 */
class SiteType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Nom']);
        $builder->add('host', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Host']);
        $builder->add('active', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['label' => 'Activé', 'required' => false]);
        $builder->add('position', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, ['label' => 'Position']);
        $builder->add('image', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['label' => 'Image', 'required' => false]);
        $builder->add('gaId', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Google Analytics']);
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Site::class]);
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'site';
    }
}
