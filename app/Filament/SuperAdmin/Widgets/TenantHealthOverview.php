<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\GeneralSetting;
use App\Models\HomepageSection;
use App\Models\Order;
use App\Models\Tenant;
use Filament\Widgets\Widget;

class TenantHealthOverview extends Widget
{
    protected static string $view = 'filament.super-admin.widgets.tenant-health-overview';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{name: string, score: int, color: string}>
     */
    public function getTenantHealthData(): array
    {
        $tenants = Tenant::query()
            ->withoutGlobalScopes()
            ->where('is_active', true)
            ->with(['products', 'users'])
            ->get();

        $tenantScores = $tenants->map(function (Tenant $tenant): array {
            $score = 0;

            if ($tenant->products->isNotEmpty()) {
                $score += 20;
            }

            $hasRecentOrders = Order::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->exists();

            if ($hasRecentOrders) {
                $score += 30;
            }

            if ($tenant->users->isNotEmpty()) {
                $score += 20;
            }

            $hasHomepageSections = HomepageSection::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if ($hasHomepageSections) {
                $score += 15;
            }

            $hasGeneralSettings = GeneralSetting::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if ($hasGeneralSettings) {
                $score += 15;
            }

            $color = match (true) {
                $score >= 70 => 'success',
                $score >= 40 => 'warning',
                default => 'danger',
            };

            return [
                'name' => $tenant->name,
                'score' => $score,
                'color' => $color,
            ];
        });

        return $tenantScores
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }
}
