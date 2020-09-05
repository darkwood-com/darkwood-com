<?php

namespace App\Form;

use App\Entity\User;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username');
        $builder->add('email', EmailType::class);
        $builder->add('plainPassword',  RepeatedType::class, array(
            'type' => PasswordType::class,
            'first_options' => array('label' => 'Mot de passe'),
            'second_options' => array('label' => 'Confirmer mot de passe'),
            'invalid_message' => 'Les mots de passe ne correspondent pas.',
            'mapped' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
            ],
        ));
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
            'data_class' => User::class,
        ]);
    }
}
