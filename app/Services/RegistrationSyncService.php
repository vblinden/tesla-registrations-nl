<?php

namespace App\Services;

use App\Models\DailyRegistrationTotal;
use App\Models\SyncState;
use App\Models\TeslaRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegistrationSyncService
{
    public function __construct(
        private readonly RdwApiService $rdwApi,
        private readonly SyncMetadataService $syncMetadata,
    ) {}

    public function sync(int $days = 14): int
    {
        $to = Carbon::today();
        $from = $to->copy()->subDays($days - 1);

        $records = $this->rdwApi->fetchAggregatedRegistrations($from, $to);
        $aggregated = $this->aggregateRecords($records);
        $dailyTotals = $this->rdwApi->fetchDailyTotalRegistrations($from, $to);

        DB::transaction(function () use ($aggregated, $dailyTotals, $from, $to) {
            TeslaRegistration::query()
                ->whereBetween('registration_date', [$from->toDateString(), $to->toDateString()])
                ->delete();

            foreach ($aggregated as $record) {
                TeslaRegistration::query()->create($record);
            }

            DailyRegistrationTotal::query()
                ->whereBetween('registration_date', [$from->toDateString(), $to->toDateString()])
                ->delete();

            foreach ($dailyTotals as $record) {
                DailyRegistrationTotal::query()->create([
                    'registration_date' => $record['registration_date'],
                    'total_count' => $record['count'],
                ]);
            }
        });

        $this->syncMetadata->recordSyncTimestamps();

        return count($aggregated);
    }

    public function syncIfUpdated(int $days = 14): ?int
    {
        $datasetUpdatedAt = $this->rdwApi->getDatasetUpdatedAt();

        if ($datasetUpdatedAt === null) {
            $this->syncMetadata->ensureMetadataIsCurrent();

            return null;
        }

        $datasetIso = $datasetUpdatedAt->toIso8601String();
        $lastKnownUpdate = SyncState::getValue(SyncMetadataService::DATASET_UPDATED_AT);

        if ($lastKnownUpdate === $datasetIso) {
            $this->syncMetadata->ensureMetadataIsCurrent($datasetIso);

            return null;
        }

        return $this->sync($days);
    }

    /**
     * @param  list<array{registration_date: string, model: string, color: string, count: int}>  $records
     * @return list<array{registration_date: string, model: string, color: string, count: int}>
     */
    private function aggregateRecords(array $records): array
    {
        $grouped = [];

        foreach ($records as $record) {
            $key = "{$record['registration_date']}|{$record['model']}|{$record['color']}";
            $grouped[$key] = [
                'registration_date' => $record['registration_date'],
                'model' => $record['model'],
                'color' => $record['color'],
                'count' => ($grouped[$key]['count'] ?? 0) + $record['count'],
            ];
        }

        return array_values($grouped);
    }
}
