<?php

namespace App\Console\Commands;

use App\Services\RegistrationSyncService;
use Illuminate\Console\Command;

class SyncRdwRegistrations extends Command
{
    protected $signature = 'rdw:sync-registrations
                            {--days=14 : Number of days to sync}
                            {--force : Sync even if RDW data has not changed}';

    protected $description = 'Sync Tesla registration data from the RDW open data API';

    public function handle(RegistrationSyncService $syncService): int
    {
        $days = (int) $this->option('days');

        if ($this->option('force')) {
            $this->info("Force syncing Tesla registrations for the last {$days} days...");
            $count = $syncService->sync($days);
            $this->info("Synced {$count} aggregated records.");

            return self::SUCCESS;
        }

        $this->info('Checking for RDW data updates...');

        $count = $syncService->syncIfUpdated($days);

        if ($count === null) {
            $this->info('No new RDW data available. Skipped.');

            return self::SUCCESS;
        }

        $this->info("RDW data updated. Synced {$count} aggregated records.");

        return self::SUCCESS;
    }
}
