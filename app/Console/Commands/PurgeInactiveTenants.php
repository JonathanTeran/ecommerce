<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class PurgeInactiveTenants extends Command
{
    protected $signature = 'tenants:purge-inactive
                            {--months=3 : Months of inactivity before purging}
                            {--dry-run : Show which tenants would be purged without deleting}';

    protected $description = 'Permanently delete tenants that have been deactivated for more than 3 months';

    public function handle(): int
    {
        $months = (int) $this->option('months');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subMonths($months);

        $tenants = Tenant::query()
            ->whereNotNull('deactivated_at')
            ->where('deactivated_at', '<=', $cutoff)
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('No inactive tenants to purge.');

            return self::SUCCESS;
        }

        $this->info("Found {$tenants->count()} tenant(s) inactive for more than {$months} months.");

        if ($dryRun) {
            $tenants->each(function (Tenant $tenant) {
                $this->line("  - {$tenant->name} (deactivated: {$tenant->deactivated_at->toDateString()})");
            });
            $this->info('Dry run complete. No data was deleted.');

            return self::SUCCESS;
        }

        $tenants->each(function (Tenant $tenant) {
            $tenantName = $tenant->name;

            $tenant->users()->forceDelete();
            $tenant->products()->forceDelete();
            $tenant->categories()->forceDelete();
            $tenant->brands()->forceDelete();
            $tenant->orders()->forceDelete();
            $tenant->subscriptions()->forceDelete();
            $tenant->generalSettings()->forceDelete();
            $tenant->forceDelete();

            $this->line("  Purged: {$tenantName}");
        });

        $this->info("Successfully purged {$tenants->count()} tenant(s).");

        return self::SUCCESS;
    }
}
