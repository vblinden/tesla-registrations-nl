<?php

use App\Models\DailyRegistrationTotal;
use App\Models\SyncState;
use App\Models\TeslaRegistration;
use App\Services\RdwApiService;
use App\Services\SyncMetadataService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('backfills last synced at from registration data', function () {
    $syncedAt = Carbon::parse('2026-07-13 08:00:00');

    TeslaRegistration::query()->forceCreate([
        'registration_date' => now()->toDateString(),
        'model' => 'MODEL Y',
        'color' => 'WIT',
        'count' => 5,
        'updated_at' => $syncedAt,
        'created_at' => $syncedAt,
    ]);

    $metadata = app(SyncMetadataService::class);

    expect($metadata->getLastSyncedAt())->toBe($syncedAt->toIso8601String());
    expect(SyncState::getValue(SyncMetadataService::LAST_SYNCED_AT))->toBe($syncedAt->toIso8601String());
});

it('fetches and stores rdw dataset updated at when missing', function () {
    $datasetUpdatedAt = Carbon::parse('2026-07-13 06:00:00');

    $this->mock(RdwApiService::class)
        ->shouldReceive('getDatasetUpdatedAt')
        ->once()
        ->andReturn($datasetUpdatedAt);

    $metadata = app(SyncMetadataService::class);

    expect($metadata->getDatasetUpdatedAt())->toBe($datasetUpdatedAt->toIso8601String());
});

it('exposes sync metadata on the dashboard', function () {
    $syncedAt = Carbon::parse('2026-07-13 08:00:00');
    $datasetUpdatedAt = Carbon::parse('2026-07-13 06:00:00');

    TeslaRegistration::query()->forceCreate([
        'registration_date' => now()->toDateString(),
        'model' => 'MODEL Y',
        'color' => 'WIT',
        'count' => 5,
        'updated_at' => $syncedAt,
        'created_at' => $syncedAt,
    ]);

    DailyRegistrationTotal::query()->create([
        'registration_date' => now()->toDateString(),
        'total_count' => 1000,
    ]);

    SyncState::setValue(SyncMetadataService::DATASET_UPDATED_AT, $datasetUpdatedAt->toIso8601String());

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('lastSyncedAt', $syncedAt->toIso8601String())
        ->where('rdwDataUpdatedAt', $datasetUpdatedAt->toIso8601String())
    );
});
