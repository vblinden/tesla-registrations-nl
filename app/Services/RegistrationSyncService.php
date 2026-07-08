<?php

namespace App\Services;

use App\Models\DailyRegistrationTotal;
use App\Models\TeslaRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RegistrationSyncService
{
    public function __construct(
        private readonly RdwApiService $rdwApi,
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

        $this->storeSyncTimestamps();

        return count($aggregated);
    }

    public function syncIfUpdated(int $days = 14): ?int
    {
        $datasetUpdatedAt = $this->rdwApi->getDatasetUpdatedAt();

        if ($datasetUpdatedAt === null) {
            return null;
        }

        $lastKnownUpdate = Cache::get('rdw_dataset_updated_at');

        if ($lastKnownUpdate === $datasetUpdatedAt->toIso8601String()) {
            return null;
        }

        return $this->sync($days);
    }

    private function storeSyncTimestamps(): void
    {
        Cache::put('rdw_last_synced_at', now()->toIso8601String());

        $datasetUpdatedAt = $this->rdwApi->getDatasetUpdatedAt();

        if ($datasetUpdatedAt !== null) {
            Cache::put('rdw_dataset_updated_at', $datasetUpdatedAt->toIso8601String());
        }
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
