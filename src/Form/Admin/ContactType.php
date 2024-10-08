<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Contact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *  Form Type.
 */
class ContactType extends AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'];
        $builder->add('email', TextType::class, ['required' => false]);
        $builder->add('website', TextType::class, ['required' => false]);
        $builder->add('content', TextareaType::class, ['required' => false]);
        /*$builder->add('user', EntityType::class, array(
              'class' => 'UserBundle:User',
              'placeholder' => '- Choisissez un utilisateur -',
              'required' => false,
          ));*/
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Contact::class, 'locale' => null]);
    }
}
