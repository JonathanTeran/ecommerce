<?php

use App\Enums\Module;
use App\Enums\PaymentMethod;
use App\Enums\PlanType;
use App\Filament\Pages\ManageSriSettings;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SriService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTenantWithPlanForSri(PlanType $planType): array
{
    $plan = Plan::create([
        'name' => $planType->label(),
        'slug' => $planType->value . '-sri-' . uniqid(),
        'type' => $planType->value,
        'price' => $planType->price(),
        'max_products' => $planType->maxProducts(),
        'max_users' => $planType->maxUsers(),
        'modules' => array_map(fn (Module $m) => $m->value, $planType->modules()),
    ]);

    $tenant = Tenant::create([
        'name' => 'Tenant ' . $planType->label(),
        'slug' => 'tenant-sri-' . $planType->value . '-' . uniqid(),
    ]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $user->assignRole('admin');

    return [$tenant, $plan, $user];
}

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

it('sri_next_sequence starts at 1 and increments after generating access key', function () {
    GeneralSetting::create(['sri_next_sequence' => 1]);

    $order = Order::factory()->create([
        'subtotal' => 10.00,
        'discount_amount' => 0,
        'tax_amount' => 1.50,
        'shipping_amount' => 0,
        'total' => 11.50,
        'payment_method' => PaymentMethod::BANK_TRANSFER,
    ]);

    OrderItem::factory()->for($order)->create([
        'sku' => 'PROD-00001',
        'name' => 'Test Product',
        'price' => 10.00,
        'quantity' => 1,
        'subtotal' => 10.00,
    ]);

    $service = new SriService;
    $service->generateAccessKey($order);

    $settings = GeneralSetting::first();
    expect($settings->sri_next_sequence)->toBe(2);
});

it('access key uses sequential from sri_next_sequence', function () {
    GeneralSetting::create(['sri_next_sequence' => 42]);

    $order = Order::factory()->create([
        'subtotal' => 10.00,
        'discount_amount' => 0,
        'tax_amount' => 1.50,
        'shipping_amount' => 0,
        'total' => 11.50,
        'payment_method' => PaymentMethod::BANK_TRANSFER,
    ]);

    OrderItem::factory()->for($order)->create([
        'sku' => 'PROD-00001',
        'name' => 'Test Product',
        'price' => 10.00,
        'quantity' => 1,
        'subtotal' => 10.00,
    ]);

    $service = new SriService;
    $accessKey = $service->generateAccessKey($order);

    // Sequential is at offset 30 (date[8]+codDoc[2]+ruc[13]+env[1]+estab[3]+ptoEmi[3] = 30)
    $sequential = substr($accessKey, 30, 9);
    expect($sequential)->toBe('000000042');

    // Next call should use 43
    $accessKey2 = $service->generateAccessKey($order);
    $sequential2 = substr($accessKey2, 30, 9);
    expect($sequential2)->toBe('000000043');

    // Sequence should now be 44
    expect(GeneralSetting::first()->sri_next_sequence)->toBe(44);
});

it('ManageSriSettings page is accessible for tenant with SriInvoicing module', function () {
    [$tenant, , $user] = createTenantWithPlanForSri(PlanType::Enterprise);

    app()->instance('current_tenant', $tenant);
    $this->actingAs($user);

    expect($tenant->hasModule(Module::SriInvoicing))->toBeTrue()
        ->and(ManageSriSettings::canAccess())->toBeTrue();
});

it('ManageSriSettings page is not accessible for tenant without SriInvoicing module', function () {
    [$tenant, , $user] = createTenantWithPlanForSri(PlanType::Basic);

    app()->instance('current_tenant', $tenant);
    $this->actingAs($user);

    expect($tenant->hasModule(Module::SriInvoicing))->toBeFalse()
        ->and(ManageSriSettings::canAccess())->toBeFalse();
});

it('ManageSriSettings page is accessible for super admin', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null]);
    $superAdmin->assignRole('super_admin');

    $this->actingAs($superAdmin);

    expect(ManageSriSettings::canAccess())->toBeTrue();
});
