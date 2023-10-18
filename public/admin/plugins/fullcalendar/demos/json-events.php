<?php

declare(strict_types=1);

$year = date('Y');
$month = date('m');

echo json_encode([
    [
        'id' => 111,
        'title' => 'Event1',
        'start' => sprintf('%s-%s-10', $year, $month),
        'url' => 'http://yahoo.com/',
    ],

    [
        'id' => 222,
        'title' => 'Event2',
        'start' => sprintf('%s-%s-20', $year, $month),
        'end' => sprintf('%s-%s-22', $year, $month),
        'url' => 'http://yahoo.com/',
    ],
]);
