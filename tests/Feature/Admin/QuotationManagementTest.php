<?php

use App\Enums\Module;
use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\QuotationResource\Pages\CreateQuotation;
use App\Filament\Resources\QuotationResource\Pages\ListQuotations;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create();
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Test Store',
        'tax_rate' => 15.00,
    ]);

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->category = Category::create([
        'name' => 'Electronics', 'slug' => 'electronics', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
});

it('can render quotations list page', function () {
    $this->get(QuotationResource::getUrl('index'))->assertSuccessful();
});

it('can render create quotation page', function () {
    $this->get(QuotationResource::getUrl('create'))->assertSuccessful();
});

it('can fill quotation form fields', function () {
    Livewire::test(CreateQuotation::class)
        ->fillForm([
            'user_id' => $this->customer->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => QuotationStatus::Pending,
        ])
        ->assertHasNoFormErrors(['user_id', 'customer_name', 'customer_email', 'status']);
});

it('can view quotation details', function () {
    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 200.00,
        'tax_amount' => 30.00,
        'total' => 230.00,
        'discount_amount' => 0,
    ]);

    $this->get(QuotationResource::getUrl('view', ['record' => $quotation]))
        ->assertSuccessful();
});

it('can list quotations in table', function () {
    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Test Client',
        'customer_email' => 'test@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'total' => 115.00,
        'discount_amount' => 0,
    ]);

    Livewire::test(ListQuotations::class)
        ->assertCanSeeTableRecords([$quotation]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherQuotation = Quotation::create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
        'customer_name' => 'Other Client',
        'customer_email' => 'other@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'total' => 115.00,
        'discount_amount' => 0,
    ]);

    $visibleQuotations = Quotation::all();
    expect($visibleQuotations->contains($otherQuotation))->toBeFalse();
});

it('auto-generates quotation number on creation', function () {
    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Auto Number',
        'customer_email' => 'auto@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 50.00,
        'tax_amount' => 7.50,
        'total' => 57.50,
        'discount_amount' => 0,
    ]);

    expect($quotation->quotation_number)->toStartWith('COT-');
});

it('quotation has correct default valid_until', function () {
    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Expiry Test',
        'customer_email' => 'expiry@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'total' => 115.00,
        'discount_amount' => 0,
    ]);

    expect($quotation->valid_until->toDateString())->toBe(now()->addDays(15)->toDateString());
});

it('can approve a quotation', function () {
    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Approve Test',
        'customer_email' => 'approve@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'total' => 115.00,
        'discount_amount' => 0,
    ]);

    $quotation->approve($this->admin);

    expect($quotation->fresh()->status)->toBe(QuotationStatus::Approved)
        ->and($quotation->fresh()->approved_by)->toBe($this->admin->id);
});

it('can reject a quotation with reason', function () {
    $quotation = Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Reject Test',
        'customer_email' => 'reject@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'total' => 115.00,
        'discount_amount' => 0,
    ]);

    $quotation->reject($this->admin, 'Price too high');

    expect($quotation->fresh()->status)->toBe(QuotationStatus::Rejected)
        ->and($quotation->fresh()->rejection_reason)->toBe('Price too high')
        ->and($quotation->fresh()->rejected_by)->toBe($this->admin->id);
});

it('shows navigation badge with pending count', function () {
    Quotation::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->customer->id,
        'customer_name' => 'Badge Test',
        'customer_email' => 'badge@example.com',
        'status' => QuotationStatus::Pending,
        'subtotal' => 100.00,
        'tax_amount' => 15.00,
        'total' => 115.00,
        'discount_amount' => 0,
    ]);

    expect(QuotationResource::getNavigationBadge())->toBe('1');
});
