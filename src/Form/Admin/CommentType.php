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
class CommentType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $builder->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['required' => false]);
        $builder->add('page', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => \App\Entity\Page::class, 'placeholder' => '- Choisissez une page -', 'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($locale) {
            return $er->createQueryBuilder('p')->select('p', 'pts')->leftJoin('p.translations', 'pts')->andWhere('pts.locale = :locale OR pts.locale IS NULL')->setParameter('locale', $locale);
        }]);
        $builder->add('active', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false]);
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Comment::class, 'locale' => null]);
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
