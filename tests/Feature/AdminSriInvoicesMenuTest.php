<?php

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('shows only electronic invoices in the sri menu', function () {
    Role::create(['name' => 'admin']);

    $tenant = Tenant::factory()->create();

    $plan = Plan::create([
        'name' => 'Enterprise',
        'slug' => 'enterprise-sri-test',
        'type' => 'enterprise',
        'price' => 99,
        'billing_period' => 'monthly',
        'modules' => ['products', 'categories', 'brands', 'orders', 'cart', 'storefront', 'sri_invoicing'],
        'is_active' => true,
    ]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'tenant_id' => $tenant->id,
    ]);
    $admin->assignRole('admin');

    $withSri = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'order_number' => 'ORD-SRI-001',
        'sri_access_key' => '2301202601120748180300110020010000000331234567819',
        'sri_authorization_status' => 'pending',
    ]);

    $withoutSri = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'order_number' => 'ORD-NO-SRI-001',
        'sri_access_key' => null,
    ]);

    $response = $this->actingAs($admin)->get('/admin/orders/facturacion-electronica');

    $response->assertSuccessful();
    $response->assertSee('Facturación Electrónica');
    $response->assertSee($withSri->order_number);
    $response->assertDontSee($withoutSri->order_number);
});
