<?php

namespace App\Form\Admin;

use App\Entity\Comment;
use App\Entity\Page;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *  Form Type.
 */
class CommentType extends AbstractType
{
    /**
     * Build Form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];

        $builder->add('content', TextareaType::class, array('required' => false));
        $builder->add('page', EntityType::class, array(
            'class' => Page::class,
            'placeholder' => '- Choisissez une page -',
            'query_builder' => function (EntityRepository $er) use ($locale) {
                return $er->createQueryBuilder('p')
                    ->select('p', 'pts')
                    ->leftJoin('p.translations', 'pts')
                    ->andWhere('pts.locale = :locale OR pts.locale IS NULL')
                    ->setParameter('locale', $locale);
            },
        ));
        $builder->add('active', CheckboxType::class, array('required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Comment::class,
            'locale' => null,
        ));
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'comment';
    }
}
