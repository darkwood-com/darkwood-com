<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/bin/.phpunit/phpunit',
    ]);

    $parameters->set(Option::SETS, [
        SetList::CODING_STYLE,
        SetList::PHP_80,
        SetList::SYMFONY_CODE_QUALITY,
        SetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SetList::SYMFONY_PHPUNIT,
        SetList::SYMFONY_AUTOWIRE,
        SetList::CONTRIBUTTE_TO_SYMFONY,
        SetList::SYMFONY_50,
        SetList::SYMFONY_50_TYPES,
        SetList::TWIG_240
    ]);

    // register single rule
    $services = $containerConfigurator->services();
    $services->set(NullableCompareToNullRector::class);
};
