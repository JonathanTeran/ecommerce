<?php

use App\Enums\Module;
use App\Filament\Pages\ReportsPage;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    Role::create(['name' => 'admin']);
});

it('admin can access reports page', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');

    $this->actingAs($admin);

    expect(ReportsPage::canAccess())->toBeTrue();
});

it('non-admin cannot access reports page', function () {
    $customer = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->actingAs($customer);

    // Replace tenant subscription with a plan without Reports module
    $basicPlan = Plan::create([
        'name' => 'Basic', 'slug' => 'basic', 'type' => 'basic',
        'price' => 30, 'modules' => ['products', 'categories'],
        'is_active' => true,
    ]);
    Subscription::query()->where('tenant_id', $this->tenant->id)->update(['plan_id' => $basicPlan->id]);

    $this->tenant->refresh();
    app()->instance('current_tenant', $this->tenant);

    expect(ReportsPage::canAccess())->toBeFalse();
});

it('displays sales data', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');

    Order::factory()->count(3)->create([
        'user_id' => $admin->id,
        'status' => \App\Enums\OrderStatus::DELIVERED,
    ]);

    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $salesReport = $page->getSalesReport();

    expect($salesReport)
        ->toHaveKeys(['total_revenue', 'total_orders', 'avg_order_value', 'daily'])
        ->and($salesReport['total_orders'])->toBe(3)
        ->and($salesReport['total_revenue'])->toBeGreaterThan(0);
});
