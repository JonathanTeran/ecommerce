<?php

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('shows sri tracking section on admin order edit page', function () {
    Role::create(['name' => 'admin']);

    $tenant = Tenant::factory()->create();

    $plan = Plan::create([
        'name' => 'Enterprise',
        'slug' => 'enterprise-test',
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

    $order = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'sri_access_key' => '2301202601120748180300110020010000000331234567819',
        'sri_authorization_status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->get('/admin/orders/'.$order->id.'/edit');

    $response->assertSuccessful();
    $response->assertSee('Facturación Electrónica');
    $response->assertSee('wire:poll.10s', false);
});
