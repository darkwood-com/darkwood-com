<?php

declare(strict_types=1);

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use EWZ\Bundle\RecaptchaBundle\EWZRecaptchaBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Liip\ImagineBundle\LiipImagineBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Sentry\SentryBundle\SentryBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;
use SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use Vich\UploaderBundle\VichUploaderBundle;

return [
    FrameworkBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    MonologBundle::class => ['all' => true],
    DebugBundle::class => ['dev' => true, 'test' => true],
    MakerBundle::class => ['dev' => true],
    VichUploaderBundle::class => ['all' => true],
    StofDoctrineExtensionsBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    LiipImagineBundle::class => ['all' => true],
    WebpackEncoreBundle::class => ['all' => true],
    EWZRecaptchaBundle::class => ['all' => true],
    KnpPaginatorBundle::class => ['all' => true],
    KnpUOAuth2ClientBundle::class => ['all' => true],
    SymfonyCastsVerifyEmailBundle::class => ['all' => true],
    SentryBundle::class => ['prod' => true],
    NelmioCorsBundle::class => ['all' => true],
    ApiPlatformBundle::class => ['all' => true],
    LexikJWTAuthenticationBundle::class => ['all' => true],
];
