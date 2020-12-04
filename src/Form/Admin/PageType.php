<?php

namespace App\Form\Admin;

use App\Entity\App;
use App\Entity\Page;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
/**
 *  Form Type.
 */
class PageType extends \Symfony\Component\Form\AbstractType
{
    /**
     * Build Form.
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $builder->add('ref', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
        $builder->add('site', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => \App\Entity\Site::class]);
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::POST_SET_DATA, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            if ($data instanceof \App\Entity\App) {
                $form->add('banner', \Vich\UploaderBundle\Form\Type\VichImageType::class, ['required' => false]);
                $form->add('theme', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false]);
                $form->add('contents', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, ['entry_type' => \App\Form\Admin\AppContentType::class, 'allow_add' => true, 'by_reference' => false, 'allow_delete' => true, 'error_bubbling' => false]);
            }
        });
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => \App\Entity\Page::class, 'locale' => null]);
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'page';
    }
}
