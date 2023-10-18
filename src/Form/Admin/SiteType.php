<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Site;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['label' => 'Nom']);
        $builder->add('host', TextType::class, ['label' => 'Host']);
        $builder->add('active', CheckboxType::class, ['label' => 'Activé', 'required' => false]);
        $builder->add('position', IntegerType::class, ['label' => 'Position']);
        $builder->add('image', VichImageType::class, ['label' => 'Image', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Site::class]);
    }
}
