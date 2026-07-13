<?php

use App\Models\TeslaRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the dashboard', function () {
    TeslaRegistration::query()->create([
        'registration_date' => now()->toDateString(),
        'model' => 'MODEL Y',
        'color' => 'WIT',
        'variant' => 'Long Range AWD',
        'count' => 5,
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('dailyAll')
        ->has('dailyAllByVariant')
        ->has('dailyByModelDetail')
        ->has('dailyByModelVariant')
        ->has('variantSummary')
        ->has('summary')
    );
});
