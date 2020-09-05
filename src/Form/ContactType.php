<?php

namespace App\Form;

use App\Entity\Contact;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, array('required' => false));
        $builder->add('website', TextType::class, array('required' => false));
        $builder->add('content', TextareaType::class, array('required' => false));
        /*$builder->add('user', EntityType::class, array(
            'class' => 'UserBundle:User',
            'placeholder' => '- Choisissez un utilisateur -',
            'required' => false,
        ));*/
        $builder->add('recaptcha', EWZRecaptchaType::class, array(
            'mapped' => false,
            'constraints' => array(
                new IsTrue(),
            ),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
