<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

return static function (array $event) {
    return 'Hello ' . ($event['name'] ?? 'world');
};