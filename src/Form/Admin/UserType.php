<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *  Form Type.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class UserType extends AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('civility', ChoiceType::class, [
            'choices' => [
                'm'    => 'Monsieur',
                'mme'  => 'Madame',
                'mlle' => 'Mademoiselle',
            ],
            'placeholder' => false,
            'required'    => false,
        ]);
        $builder->add('firstname', TextType::class);
        $builder->add('lastname', TextType::class);
        $builder->add('username', TextType::class);
        $builder->add('email', EmailType::class);
        $builder->add('plainPassword', RepeatedType::class, [
            'type'            => PasswordType::class,
            'options'         => ['required' => false],
            'first_options'   => ['label' => 'Mot de passe'],
            'second_options'  => ['label' => 'Confirmer mot de passe'],
            'invalid_message' => 'Mot de passe invalide',
        ]);

        $builder->add('birthday', DateType::class, [
            'widget' => 'single_text',
            'input'  => 'datetime',
            'format' => 'dd/MM/y',
        ]);

        $builder->add('isVerified', CheckboxType::class, ['required' => false]);

        $builder->add('hasRoleAdmin', CheckboxType::class, ['required' => false]);
        $builder->add('hasRoleSuperAdmin', CheckboxType::class, ['required' => false]);
    }

    /**
     * Defualt options.
     *
     * @param ExceptionInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => User::class,
            'validation_groups' => ['Default', 'Admin'],
            'intention'         => 'Admin',
        ]);
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'user';
    }
}
