<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', TextType::class, [
            'required' => false,
        ]);
        $builder->add('lastname', TextType::class, [
            'required' => false,
        ]);
        $builder->add('birthday', DateType::class, [
            'required' => false,
            'widget' => 'single_text',
        ]);
        $builder->add('city', TextType::class, [
            'required' => false,
        ]);
        $builder->add('comment', TextareaType::class, [
            'required' => false,
        ]);
        $builder->add('image', FileType::class, [
            'required' => false,
        ]);
        $builder->add('current_password', PasswordType::class, [
            'mapped' => false,
            'required' => false,
        ]);
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'fos_user.password.mismatch',
            'required' => false,
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
