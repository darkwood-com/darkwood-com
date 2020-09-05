<?php

namespace App\Form\Admin;

use App\Entity\App;
use App\Entity\Page;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
class PageType extends AbstractType
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

        $builder->add('ref', TextType::class);
        $builder->add('site', EntityType::class, [
            'class' => Site::class,
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            if ($data instanceof App) {
                $form->add('banner', VichImageType::class, array(
                    'required' => false,
                ));
                $form->add('theme', TextType::class, array(
                    'required' => false,
                ));
                $form->add('contents', CollectionType::class, array(
                    'entry_type' => AppContentType::class,
                    'allow_add' => true,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'error_bubbling' => false,
                ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Page::class,
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
        return 'page';
    }
}
