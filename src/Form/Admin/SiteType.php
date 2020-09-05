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
class SiteType extends AbstractType
{
    /**
     * Build Form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array('label' => 'Nom'));
        $builder->add('host', TextType::class, array('label' => 'Host'));
        $builder->add('active', CheckboxType::class, array('label' => 'Activé', 'required' => false));
        $builder->add('position', IntegerType::class, array('label' => 'Position'));
        $builder->add('image', VichImageType::class, array(
            'label' => 'Image',
            'required' => false,
        ));
        $builder->add('gaId', TextType::class, array('label' => 'Google Analytics'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Site::class,
        ));
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
