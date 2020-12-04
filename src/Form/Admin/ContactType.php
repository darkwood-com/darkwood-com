<?php

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
class ContactType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $builder->add('email', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false]);
        $builder->add('website', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false]);
        $builder->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['required' => false]);
        /*$builder->add('user', EntityType::class, array(
              'class' => 'UserBundle:User',
              'placeholder' => '- Choisissez un utilisateur -',
              'required' => false,
          ));*/
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Contact::class, 'locale' => null]);
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'contact';
    }
}
