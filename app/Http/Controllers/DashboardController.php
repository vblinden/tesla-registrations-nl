<?php

namespace App\Http\Controllers;

use App\Models\TeslaRegistration;
use App\Services\SyncMetadataService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const array TRACKED_MODELS = ['MODEL Y', 'MODEL 3', 'MODEL S', 'MODEL X'];

    public function index(SyncMetadataService $syncMetadata): Response
    {
        $days = 14;
        $from = Carbon::today()->subDays($days - 1);
        $to = Carbon::today();

        $dateRange = collect(range(0, $days - 1))
            ->map(fn (int $offset) => $from->copy()->addDays($offset)->toDateString())
            ->all();

        $registrations = TeslaRegistration::query()
            ->whereBetween('registration_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('registration_date')
            ->get();

        $colors = $registrations->pluck('color')->unique()->sort()->values()->all();
        $variants = $registrations->pluck('variant')->unique()->sort()->values()->all();
        $models = $registrations->pluck('model')->unique()->sort()->values()->all();

        $dailyAll = $this->buildDailyChartData($dateRange, $registrations, null, $colors);
        $dailyAllByVariant = $this->buildDailyChartData($dateRange, $registrations, null, $variants, 'variant');
        $dailyByModelDetail = collect(self::TRACKED_MODELS)->mapWithKeys(
            fn (string $model) => [$model => $this->buildDailyChartData($dateRange, $registrations, $model, $colors)]
        )->all();
        $dailyByModelVariant = collect(self::TRACKED_MODELS)->mapWithKeys(function (string $model) use ($dateRange, $registrations, $variants) {
            $modelVariants = $registrations
                ->where('model', $model)
                ->pluck('variant')
                ->unique()
                ->sort()
                ->values()
                ->all();

            return [
                $model => $this->buildDailyChartData($dateRange, $registrations, $model, $modelVariants, 'variant'),
            ];
        })->all();
        $variantSummary = collect(self::TRACKED_MODELS)->mapWithKeys(function (string $model) use ($registrations) {
            return [
                $model => $registrations
                    ->where('model', $model)
                    ->groupBy('variant')
                    ->map(fn ($records) => $records->sum('count'))
                    ->sortDesc()
                    ->all(),
            ];
        })->all();
        $dailyByModel = collect($models)->mapWithKeys(function (string $model) use ($dateRange, $registrations) {
            $modelRegs = $registrations->where('model', $model);

            return [
                $model => collect($dateRange)->map(function (string $date) use ($modelRegs) {
                    $dayTotal = $modelRegs
                        ->filter(fn ($record) => $record->registration_date->toDateString() === $date)
                        ->sum('count');

                    return [
                        'date' => $date,
                        'label' => Carbon::parse($date)->locale('nl')->isoFormat('D MMM'),
                        'total' => $dayTotal,
                    ];
                })->values()->all(),
            ];
        })->all();

        $summary = [
            'modelY' => $registrations->where('model', 'MODEL Y')->sum('count'),
            'model3' => $registrations->where('model', 'MODEL 3')->sum('count'),
            'modelS' => $registrations->where('model', 'MODEL S')->sum('count'),
            'modelX' => $registrations->where('model', 'MODEL X')->sum('count'),
        ];

        return Inertia::render('Dashboard', [
            'dailyAll' => $dailyAll,
            'dailyAllByVariant' => $dailyAllByVariant,
            'dailyByModelDetail' => $dailyByModelDetail,
            'dailyByModelVariant' => $dailyByModelVariant,
            'dailyByModel' => $dailyByModel,
            'colors' => $colors,
            'variants' => $variants,
            'variantSummary' => $variantSummary,
            'models' => $models,
            'summary' => $summary,
            'lastSyncedAt' => $syncMetadata->getLastSyncedAt(),
            'rdwDataUpdatedAt' => $syncMetadata->getDatasetUpdatedAt(),
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }

    /**
     * @param  list<string>  $dateRange
     * @param  list<string>  $segments
     * @return list<array<string, mixed>>
     */
    private function buildDailyChartData(
        array $dateRange,
        $registrations,
        ?string $model,
        array $segments,
        string $segmentKey = 'color',
    ): array {
        $filtered = $model
            ? $registrations->where('model', $model)
            : $registrations;

        return collect($dateRange)->map(function (string $date) use ($filtered, $segments, $segmentKey) {
            $dayRecords = $filtered->filter(
                fn ($record) => $record->registration_date->toDateString() === $date
            );

            $row = [
                'date' => $date,
                'label' => Carbon::parse($date)->locale('nl')->isoFormat('D MMM'),
                'total' => $dayRecords->sum('count'),
            ];

            foreach ($segments as $segment) {
                $row[$segment] = $dayRecords->where($segmentKey, $segment)->sum('count');
            }

            return $row;
        })->values()->all();
    }
}
