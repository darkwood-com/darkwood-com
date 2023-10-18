<?php

declare(strict_types=1);

namespace App\Tools;

use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(description: 'Instant Upgrades and Automated Refactoring', aliases: ['rector'])]
function rector(): int
{
    return run(
        [__DIR__ . '/vendor/bin/rector process'],
        tty: true,
        allowFailure: true,
    )->getExitCode();
}
