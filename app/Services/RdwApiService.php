<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RdwApiService
{
    private const string BASE_URL = 'https://opendata.rdw.nl/resource/m9d7-ebf2.json';

    private const string METADATA_URL = 'https://opendata.rdw.nl/api/views/m9d7-ebf2.json';

    public function getDatasetUpdatedAt(): ?Carbon
    {
        $response = Http::timeout(30)->get(self::METADATA_URL);

        if (! $response->successful()) {
            return null;
        }

        $timestamp = $response->json('rowsUpdatedAt');

        if (! is_int($timestamp)) {
            return null;
        }

        return Carbon::createFromTimestampUTC($timestamp)->timezone(config('app.timezone'));
    }

    /**
     * @return list<array{registration_date: string, model: string, color: string, count: int}>
     */
    public function fetchAggregatedRegistrations(Carbon $from, Carbon $to): array
    {
        $fromDate = $from->format('Ymd');
        $toDate = $to->format('Ymd');

        $response = Http::timeout(120)
            ->get(self::BASE_URL, [
                '$select' => 'datum_tenaamstelling, handelsbenaming, eerste_kleur, count(*)',
                '$where' => "merk = 'TESLA' AND datum_tenaamstelling >= '{$fromDate}' AND datum_tenaamstelling <= '{$toDate}'",
                '$group' => 'datum_tenaamstelling, handelsbenaming, eerste_kleur',
                '$order' => 'datum_tenaamstelling ASC',
                '$limit' => 10000,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('RDW API request failed: '.$response->status());
        }

        $records = [];

        foreach ($response->json() as $row) {
            if (empty($row['datum_tenaamstelling'])) {
                continue;
            }

            $records[] = [
                'registration_date' => Carbon::createFromFormat('Ymd', $row['datum_tenaamstelling'])->toDateString(),
                'model' => $this->normalizeModel($row['handelsbenaming'] ?? 'ONBEKEND'),
                'color' => strtoupper(trim($row['eerste_kleur'] ?? 'ONBEKEND')),
                'count' => (int) ($row['count'] ?? 0),
            ];
        }

        return $records;
    }

    /**
     * @return list<array{registration_date: string, count: int}>
     */
    public function fetchDailyTotalRegistrations(Carbon $from, Carbon $to): array
    {
        $fromDate = $from->format('Ymd');
        $toDate = $to->format('Ymd');

        $response = Http::timeout(120)
            ->get(self::BASE_URL, [
                '$select' => 'datum_tenaamstelling, count(*)',
                '$where' => "voertuigsoort = 'Personenauto' AND datum_tenaamstelling >= '{$fromDate}' AND datum_tenaamstelling <= '{$toDate}'",
                '$group' => 'datum_tenaamstelling',
                '$order' => 'datum_tenaamstelling ASC',
                '$limit' => 10000,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('RDW API request failed: '.$response->status());
        }

        $records = [];

        foreach ($response->json() as $row) {
            if (empty($row['datum_tenaamstelling'])) {
                continue;
            }

            $records[] = [
                'registration_date' => Carbon::createFromFormat('Ymd', $row['datum_tenaamstelling'])->toDateString(),
                'count' => (int) ($row['count'] ?? 0),
            ];
        }

        return $records;
    }

    public function normalizeModel(string $model): string
    {
        $normalized = strtoupper(trim($model));

        return match (true) {
            str_contains($normalized, 'MODEL Y') => 'MODEL Y',
            str_contains($normalized, 'MODEL 3') => 'MODEL 3',
            str_contains($normalized, 'MODEL S') => 'MODEL S',
            str_contains($normalized, 'MODEL X') => 'MODEL X',
            str_contains($normalized, 'CYBERTRUCK') => 'CYBERTRUCK',
            str_contains($normalized, 'ROADSTER') => 'ROADSTER',
            default => $normalized,
        };
    }
}
