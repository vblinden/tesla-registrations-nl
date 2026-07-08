<?php

use App\Models\DailyRegistrationTotal;
use App\Models\TeslaRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the dashboard', function () {
    TeslaRegistration::query()->create([
        'registration_date' => now()->toDateString(),
        'model' => 'MODEL Y',
        'color' => 'WIT',
        'count' => 5,
    ]);

    DailyRegistrationTotal::query()->create([
        'registration_date' => now()->toDateString(),
        'total_count' => 1000,
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('dailyAll')
        ->has('dailyByModelDetail')
        ->has('dailyMarket', 14)
        ->has('summary')
    );
});
