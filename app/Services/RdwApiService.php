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
     * @return list<array{registration_date: string, model: string, color: string, variant: string, count: int}>
     */
    public function fetchAggregatedRegistrations(Carbon $from, Carbon $to): array
    {
        $fromDate = $from->format('Ymd');
        $toDate = $to->format('Ymd');

        $response = Http::timeout(120)
            ->get(self::BASE_URL, [
                '$select' => 'datum_tenaamstelling, handelsbenaming, eerste_kleur, variant, count(*)',
                '$where' => "merk = 'TESLA' AND datum_tenaamstelling >= '{$fromDate}' AND datum_tenaamstelling <= '{$toDate}'",
                '$group' => 'datum_tenaamstelling, handelsbenaming, eerste_kleur, variant',
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

            $tradeName = $row['handelsbenaming'] ?? 'ONBEKEND';
            $variantCode = $row['variant'] ?? '';

            $records[] = [
                'registration_date' => Carbon::createFromFormat('Ymd', $row['datum_tenaamstelling'])->toDateString(),
                'model' => $this->normalizeModel($tradeName),
                'color' => strtoupper(trim($row['eerste_kleur'] ?? 'ONBEKEND')),
                'variant' => $this->normalizeVariant($variantCode, $tradeName),
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

    public function normalizeVariant(string $variant, string $handelsbenaming = ''): string
    {
        $variant = strtoupper(trim($variant));
        $tradeName = strtoupper(trim($handelsbenaming));

        if (str_contains($tradeName, 'LONG RANGE') && (str_contains($tradeName, 'AWD') || str_contains($tradeName, 'DUAL'))) {
            return 'Long Range AWD';
        }

        if (str_contains($tradeName, 'LONG RANGE')) {
            return 'Long Range RWD';
        }

        if (str_contains($tradeName, 'PERFORMANCE')) {
            return 'Performance';
        }

        if (str_contains($tradeName, 'DUAL MOTOR') || str_contains($tradeName, 'AWD')) {
            return 'AWD';
        }

        if ($variant === '' || $variant === 'N/A') {
            return 'Onbekend';
        }

        if ($variant === '100X') {
            return 'Plaid';
        }

        if (str_contains($variant, 'P100D')) {
            return 'Performance';
        }

        if ($variant === '100D' || $variant === '75D') {
            return 'Long Range AWD';
        }

        if ($variant === '90D') {
            return 'Performance AWD';
        }

        if ($variant === '75R') {
            return 'Standard Range RWD';
        }

        if ($variant === '75X') {
            return 'AWD';
        }

        if (str_starts_with($variant, 'E') && str_ends_with($variant, 'D')) {
            return 'Long Range AWD';
        }

        if (str_starts_with($variant, 'E') && str_ends_with($variant, 'LR')) {
            return 'Long Range RWD';
        }

        if (str_starts_with($variant, 'H') && preg_match('/(?:MD|LD|CD)$/', $variant)) {
            return 'Long Range AWD';
        }

        $drive = null;
        $range = null;

        if (preg_match('/(?:MD|LD|CD)$/', $variant) || preg_match('/D$/', $variant)) {
            $drive = 'AWD';
        } elseif (preg_match('/(?:LR|MR|CR|LN)$/', $variant) || preg_match('/R$/', $variant)) {
            $drive = 'RWD';
        }

        if (preg_match('/(?:LR|LD)$/', $variant) || str_contains($variant, '8LR')) {
            $range = 'Long Range';
        } elseif (preg_match('/MR$/', $variant)) {
            $range = 'Standard';
        }

        if ($drive === 'AWD' && preg_match('/(?:MD|LD)$/', $variant)) {
            $range = 'Long Range';
        }

        $parts = array_values(array_filter([$range, $drive]));

        return $parts !== [] ? implode(' ', $parts) : $variant;
    }
}
