<?php

use App\Enums\Module;
use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Filament\Resources\ProductReturnResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    $this->tenant = Tenant::factory()->create(['slug' => 'return-test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-ret-'.uniqid(),
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

function createOrderWithItems(User $user): Order
{
    $category = Category::firstOrCreate(['slug' => 'ret-test-cat'], ['name' => 'Test Category']);
    $product = Product::create([
        'name' => 'Test Product', 'slug' => 'ret-product-'.uniqid(),
        'sku' => 'RET-'.strtoupper(uniqid()), 'price' => 100,
        'quantity' => 20, 'category_id' => $category->id,
    ]);

    $order = Order::factory()->create(['user_id' => $user->id, 'tenant_id' => app('current_tenant')->id]);
    $order->items()->create([
        'product_id' => $product->id, 'name' => $product->name,
        'sku' => $product->sku, 'price' => 100, 'quantity' => 2, 'subtotal' => 200,
    ]);

    return $order->load('items');
}

it('generates return number automatically', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = createOrderWithItems($user);

    $return = ProductReturn::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'reason' => ReturnReason::Defective,
        'status' => ReturnStatus::Requested,
        'description' => 'Product arrived damaged',
        'tenant_id' => $this->tenant->id,
    ]);

    expect($return->return_number)->toStartWith('RMA-');
});

it('creates a return with items', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = createOrderWithItems($user);

    $return = ProductReturn::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'reason' => ReturnReason::Defective,
        'status' => ReturnStatus::Requested,
        'description' => 'Product arrived damaged',
        'tenant_id' => $this->tenant->id,
    ]);

    $orderItem = $order->items->first();
    $return->items()->create([
        'order_item_id' => $orderItem->id,
        'product_id' => $orderItem->product_id,
        'quantity' => 1,
        'condition' => 'damaged',
    ]);

    expect($return->items)->toHaveCount(1)
        ->and($return->order->id)->toBe($order->id)
        ->and($return->user->id)->toBe($user->id)
        ->and($return->reason)->toBe(ReturnReason::Defective);
});

it('transitions status correctly', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = createOrderWithItems($user);

    $return = ProductReturn::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'reason' => ReturnReason::WrongItem,
        'status' => ReturnStatus::Requested,
        'tenant_id' => $this->tenant->id,
    ]);

    // Requested -> Approved
    expect($return->updateStatus(ReturnStatus::Approved, 'Approved by admin'))->toBeTrue();
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Approved)
        ->and($return->approved_at)->not->toBeNull();

    // Approved -> ItemReceived
    expect($return->updateStatus(ReturnStatus::ItemReceived))->toBeTrue();
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::ItemReceived)
        ->and($return->received_at)->not->toBeNull();

    // ItemReceived -> Refunded
    expect($return->updateStatus(ReturnStatus::Refunded))->toBeTrue();
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Refunded)
        ->and($return->refunded_at)->not->toBeNull();

    // Refunded -> Closed
    expect($return->updateStatus(ReturnStatus::Closed))->toBeTrue();
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Closed)
        ->and($return->closed_at)->not->toBeNull();
});

it('prevents invalid status transitions', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = createOrderWithItems($user);

    $return = ProductReturn::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'reason' => ReturnReason::Defective,
        'status' => ReturnStatus::Requested,
        'tenant_id' => $this->tenant->id,
    ]);

    // Cannot go directly from Requested to Refunded
    expect($return->updateStatus(ReturnStatus::Refunded))->toBeFalse();
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Requested);
});

it('rejects a return', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = createOrderWithItems($user);

    $return = ProductReturn::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'reason' => ReturnReason::ChangedMind,
        'status' => ReturnStatus::Requested,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($return->updateStatus(ReturnStatus::Rejected, 'No return policy for this item'))->toBeTrue();
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Rejected)
        ->and($return->admin_notes)->toBe('No return policy for this item');
});

it('scopes pending returns', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = createOrderWithItems($user);

    ProductReturn::create([
        'order_id' => $order->id, 'user_id' => $user->id,
        'reason' => ReturnReason::Defective, 'status' => ReturnStatus::Requested,
        'tenant_id' => $this->tenant->id,
    ]);

    ProductReturn::create([
        'order_id' => $order->id, 'user_id' => $user->id,
        'reason' => ReturnReason::Damaged, 'status' => ReturnStatus::Approved,
        'tenant_id' => $this->tenant->id,
    ]);

    expect(ProductReturn::pending()->count())->toBe(1);
});

it('resource is accessible with Returns module', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect(ProductReturnResource::canAccess())->toBeTrue();
});

it('resource is NOT accessible without Returns module', function () {
    $plan = Plan::create([
        'name' => 'Basic', 'slug' => 'basic-no-ret-'.uniqid(),
        'type' => 'basic', 'price' => 30,
        'modules' => ['products', 'categories'],
        'is_active' => true,
    ]);
    $tenant = Tenant::create(['name' => 'No Returns', 'slug' => 'no-ret-'.uniqid()]);
    Subscription::create([
        'tenant_id' => $tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect(ProductReturnResource::canAccess())->toBeFalse();
});
