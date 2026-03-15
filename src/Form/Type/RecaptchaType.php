<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * reCAPTCHA v2 form type (replaces excelwebzone/recaptcha-bundle for Symfony 8).
 * Use same env vars: EWZ_RECAPTCHA_SITE_KEY, EWZ_RECAPTCHA_SECRET.
 */
final class RecaptchaType extends AbstractType
{
    public function __construct(
        private readonly string $siteKey,
        private readonly bool $enabled,
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['public_key'] = $this->siteKey;
        $view->vars['ewz_recaptcha_enabled'] = $this->enabled;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'compound' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'recaptcha';
    }
}
