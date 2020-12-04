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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];
        $builder->add('ref', TextType::class);
        $builder->add('site', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => Site::class]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            if ($data instanceof App) {
                $form->add('banner', VichImageType::class, ['required' => false]);
                $form->add('theme', TextType::class, ['required' => false]);
                $form->add('contents', CollectionType::class, ['entry_type' => \App\Form\Admin\AppContentType::class, 'allow_add' => true, 'by_reference' => false, 'allow_delete' => true, 'error_bubbling' => false]);
            }
        });
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Page::class, 'locale' => null]);
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
