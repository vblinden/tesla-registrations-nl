<?php

use App\Services\RdwApiService;

it('maps tesla variant codes to readable labels', function (string $code, string $expected) {
    expect(app(RdwApiService::class)->normalizeVariant($code))->toBe($expected);
})->with([
    ['YS5LR', 'Long Range RWD'],
    ['YS5MR', 'Standard RWD'],
    ['YS5MD', 'Long Range AWD'],
    ['YS5LD', 'Long Range AWD'],
    ['YB6MR', 'Standard RWD'],
    ['Y5LD', 'Long Range AWD'],
    ['Y7CR', 'RWD'],
    ['E3D', 'Long Range AWD'],
    ['E1R', 'RWD'],
    ['E6LR', 'Long Range RWD'],
    ['E6R', 'RWD'],
    ['H6MR', 'Standard RWD'],
    ['H6LR', 'Long Range RWD'],
    ['H5MD', 'Long Range AWD'],
    ['75D', 'Long Range AWD'],
    ['75R', 'Standard Range RWD'],
    ['100X', 'Plaid'],
    ['P100D', 'Performance'],
    ['', 'Onbekend'],
    ['N/A', 'Onbekend'],
]);
