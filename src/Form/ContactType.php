<?php

declare(strict_types=1);

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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, ['required' => false]);
        $builder->add('website', TextType::class, ['required' => false]);
        $builder->add('content', TextareaType::class, ['required' => false]);
        /*$builder->add('user', EntityType::class, array(
            'class' => 'UserBundle:User',
            'placeholder' => '- Choisissez un utilisateur -',
            'required' => false,
        ));*/
        $builder->add('recaptcha', EWZRecaptchaType::class, [
            'mapped' => false,
            'constraints' => [
                new IsTrue(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
