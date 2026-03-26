<?php

use App\Enums\Module;
use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Filament\Pages\ReportsPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create(['slug' => 'reports-adv-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-rep-'.uniqid(),
        'type' => 'enterprise', 'price' => 150,
        'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);
});

it('returns order status breakdown', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Order::factory()->count(3)->create([
        'user_id' => $user->id,
        'status' => \App\Enums\OrderStatus::PENDING,
        'tenant_id' => $this->tenant->id,
    ]);
    Order::factory()->count(2)->create([
        'user_id' => $user->id,
        'status' => \App\Enums\OrderStatus::DELIVERED,
        'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $breakdown = $page->getOrderStatusBreakdown();

    expect($breakdown)->not->toBeEmpty()
        ->and($breakdown->sum('count'))->toBe(5);
});

it('returns inventory report', function () {
    $category = Category::firstOrCreate(['slug' => 'rep-cat'], ['name' => 'Test Cat']);

    Product::create([
        'name' => 'In Stock', 'slug' => 'in-stock-'.uniqid(),
        'sku' => 'IS-'.uniqid(), 'price' => 100,
        'quantity' => 50, 'low_stock_threshold' => 5,
        'category_id' => $category->id, 'tenant_id' => $this->tenant->id,
    ]);
    Product::create([
        'name' => 'Low Stock', 'slug' => 'low-stock-'.uniqid(),
        'sku' => 'LS-'.uniqid(), 'price' => 200,
        'quantity' => 3, 'low_stock_threshold' => 5,
        'category_id' => $category->id, 'tenant_id' => $this->tenant->id,
    ]);
    Product::create([
        'name' => 'Out of Stock', 'slug' => 'oos-'.uniqid(),
        'sku' => 'OOS-'.uniqid(), 'price' => 150,
        'quantity' => 0, 'low_stock_threshold' => 5,
        'category_id' => $category->id, 'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $report = $page->getInventoryReport();

    expect($report['low_stock'])->toBe(1)
        ->and($report['out_of_stock'])->toBe(1)
        ->and($report['total_value'])->toBeGreaterThan(0)
        ->and($report['top_low_stock'])->toHaveCount(1);
});

it('returns returns report', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $this->tenant->id,
    ]);

    ProductReturn::create([
        'order_id' => $order->id, 'user_id' => $user->id,
        'reason' => ReturnReason::Defective,
        'status' => ReturnStatus::Requested,
        'refund_amount' => 50.00,
        'tenant_id' => $this->tenant->id,
    ]);
    ProductReturn::create([
        'order_id' => $order->id, 'user_id' => $user->id,
        'reason' => ReturnReason::Defective,
        'status' => ReturnStatus::Refunded,
        'refund_amount' => 100.00,
        'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $report = $page->getReturnsReport();

    expect($report['total'])->toBe(2)
        ->and($report['pending'])->toBe(1)
        ->and($report['by_reason'])->not->toBeEmpty();
});

it('returns support report', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'Open ticket',
        'status' => 'open', 'priority' => 'medium',
        'tenant_id' => $this->tenant->id,
    ]);
    SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'Resolved ticket',
        'status' => 'resolved', 'priority' => 'low',
        'resolved_at' => now(),
        'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $report = $page->getSupportReport();

    expect($report['total'])->toBe(2)
        ->and($report['open'])->toBe(1)
        ->and($report['resolved'])->toBe(1);
});

it('returns revenue by month', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Order::factory()->count(3)->create([
        'user_id' => $user->id,
        'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $revenue = $page->getRevenueByMonth();

    expect($revenue)->not->toBeEmpty()
        ->and($revenue->first()->revenue)->toBeGreaterThan(0);
});

it('returns new vs returning customers', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    Order::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $this->tenant->id,
    ]);

    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $page = new ReportsPage;
    $page->mount();

    $customers = $page->getNewVsReturningCustomers();

    expect($customers)->toHaveKeys(['total', 'new', 'returning'])
        ->and($customers['total'])->toBeGreaterThanOrEqual(1);
});
