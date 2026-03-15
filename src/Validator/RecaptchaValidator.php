<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraints\Recaptcha as RecaptchaConstraint;
use ReCaptcha\ReCaptcha;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function is_string;

final class RecaptchaValidator extends ConstraintValidator
{
    private const DISABLED_SECRET = 'recaptcha_disabled';

    public function __construct(
        #[Autowire(service: 'app.recaptcha')]
        private readonly ReCaptcha $reCaptcha,
        private readonly RequestStack $requestStack,
        #[Autowire(param: 'recaptcha.secret')]
        private readonly string $secret,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RecaptchaConstraint) {
            throw new UnexpectedTypeException($constraint, RecaptchaConstraint::class);
        }

        if ($this->secret === self::DISABLED_SECRET || $this->secret === '') {
            return;
        }

        $token = is_string($value) ? trim($value) : '';
        if ($token === '' && $this->requestStack->getCurrentRequest()) {
            $token = trim((string) $this->requestStack->getCurrentRequest()->request->get('g-recaptcha-response', ''));
        }
        if ($token === '') {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;

            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $remoteIp = $request?->getClientIp() ?? '';

        $response = $this->reCaptcha->verify($token, $remoteIp);
        if (!$response->isSuccess()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
