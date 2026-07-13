<?php

namespace App\Services;

use App\Models\DailyRegistrationTotal;
use App\Models\SyncState;
use App\Models\TeslaRegistration;
use Carbon\Carbon;

class SyncMetadataService
{
    public const string LAST_SYNCED_AT = 'rdw_last_synced_at';

    public const string DATASET_UPDATED_AT = 'rdw_dataset_updated_at';

    public function __construct(
        private readonly RdwApiService $rdwApi,
    ) {}

    public function getLastSyncedAt(): ?string
    {
        $value = SyncState::getValue(self::LAST_SYNCED_AT);

        if ($value !== null) {
            return $value;
        }

        return $this->backfillLastSyncedAt();
    }

    public function getDatasetUpdatedAt(): ?string
    {
        $value = SyncState::getValue(self::DATASET_UPDATED_AT);

        if ($value !== null) {
            return $value;
        }

        $datasetUpdatedAt = $this->rdwApi->getDatasetUpdatedAt();

        if ($datasetUpdatedAt === null) {
            return null;
        }

        $iso = $datasetUpdatedAt->toIso8601String();
        SyncState::setValue(self::DATASET_UPDATED_AT, $iso);

        return $iso;
    }

    public function recordSyncTimestamps(): void
    {
        SyncState::setValue(self::LAST_SYNCED_AT, now()->toIso8601String());

        $datasetUpdatedAt = $this->rdwApi->getDatasetUpdatedAt();

        if ($datasetUpdatedAt !== null) {
            SyncState::setValue(self::DATASET_UPDATED_AT, $datasetUpdatedAt->toIso8601String());
        }
    }

    public function ensureMetadataIsCurrent(?string $datasetUpdatedAt = null): void
    {
        $datasetUpdatedAt ??= $this->rdwApi->getDatasetUpdatedAt()?->toIso8601String();

        if ($datasetUpdatedAt !== null) {
            SyncState::setValue(self::DATASET_UPDATED_AT, $datasetUpdatedAt);
        }

        $this->backfillLastSyncedAt();
    }

    private function backfillLastSyncedAt(): ?string
    {
        $lastUpdated = collect([
            TeslaRegistration::query()->max('updated_at'),
            DailyRegistrationTotal::query()->max('updated_at'),
        ])->filter()->max();

        if ($lastUpdated === null) {
            return null;
        }

        $iso = Carbon::parse($lastUpdated)->toIso8601String();
        SyncState::setValue(self::LAST_SYNCED_AT, $iso);

        return $iso;
    }
}
