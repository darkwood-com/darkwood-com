<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\RecaptchaValidator;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Recaptcha extends Constraint
{
    public string $message = 'Please complete the captcha.';

    public function validatedBy(): string
    {
        return RecaptchaValidator::class;
    }
}
