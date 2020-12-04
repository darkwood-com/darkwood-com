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
class UserType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('civility', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['choices' => ['m' => 'Monsieur', 'mme' => 'Madame', 'mlle' => 'Mademoiselle'], 'placeholder' => false, 'required' => false]);
        $builder->add('firstname', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('lastname', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('username', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('email', \Symfony\Component\Form\Extension\Core\Type\EmailType::class);
        $builder->add('plainPassword', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, ['type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, 'options' => ['required' => false], 'first_options' => ['label' => 'Mot de passe'], 'second_options' => ['label' => 'Confirmer mot de passe'], 'invalid_message' => 'Mot de passe invalide']);
        $builder->add('birthday', \Symfony\Component\Form\Extension\Core\Type\DateType::class, ['widget' => 'single_text', 'input' => 'datetime', 'format' => 'dd/MM/y']);
        $builder->add('isVerified', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false]);
        $builder->add('hasRoleAdmin', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false]);
        $builder->add('hasRoleSuperAdmin', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false]);
    }
    /**
     * Defualt options.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\User::class, 'validation_groups' => ['Default', 'Admin'], 'intention' => 'Admin']);
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
