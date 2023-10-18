<?php

require __DIR__ . '/vendor/autoload.php';

return function (array $event) {
    return 'Hello ' . ($event['name'] ?? 'world');
};
