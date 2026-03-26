<?php

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app()->instance('current_tenant', $this->tenant);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'points_balance' => 0,
    ]);

    $this->program = LoyaltyProgram::factory()->create([
        'tenant_id' => $this->tenant->id,
        'points_per_dollar' => 1.00,
        'redemption_rate' => 0.01,
        'minimum_redemption_points' => 100,
        'is_active' => true,
    ]);
});

it('awards points for an order', function () {
    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'subtotal' => 150.00,
        'total' => 165.00,
    ]);

    $service = app(LoyaltyService::class);
    $transaction = $service->awardPoints($this->user, $order);

    expect($transaction)->not->toBeNull();
    expect($transaction->points)->toBe(150);
    expect($transaction->type)->toBe('earned');
    expect($this->user->fresh()->points_balance)->toBe(150);
});

it('redeems points', function () {
    $this->user->update(['points_balance' => 500]);

    $service = app(LoyaltyService::class);
    $transaction = $service->redeemPoints($this->user, 200);

    expect($transaction)->not->toBeNull();
    expect($transaction->points)->toBe(-200);
    expect($transaction->type)->toBe('redeemed');
    expect($this->user->fresh()->points_balance)->toBe(300);
});

it('prevents redemption below minimum', function () {
    $this->user->update(['points_balance' => 50]);

    $service = app(LoyaltyService::class);
    $transaction = $service->redeemPoints($this->user, 50);

    expect($transaction)->toBeNull();
});

it('prevents redemption exceeding balance', function () {
    $this->user->update(['points_balance' => 100]);

    $service = app(LoyaltyService::class);
    $transaction = $service->redeemPoints($this->user, 200);

    expect($transaction)->toBeNull();
});

it('calculates redemption value correctly', function () {
    $service = app(LoyaltyService::class);

    $value = $service->calculateRedemptionValue(1000);
    expect($value)->toBe(10.00);
});

it('does not award points when program is inactive', function () {
    $this->program->update(['is_active' => false]);

    $order = Order::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'subtotal' => 100.00,
    ]);

    $service = app(LoyaltyService::class);
    $transaction = $service->awardPoints($this->user, $order);

    expect($transaction)->toBeNull();
    expect($this->user->fresh()->points_balance)->toBe(0);
});

it('adjusts points manually', function () {
    $service = app(LoyaltyService::class);
    $transaction = $service->adjustPoints($this->user, 500, 'Bonus de bienvenida');

    expect($transaction->points)->toBe(500);
    expect($transaction->type)->toBe('adjusted');
    expect($this->user->fresh()->points_balance)->toBe(500);
});

it('isolates loyalty programs by tenant', function () {
    $otherTenant = Tenant::factory()->create();
    app()->instance('current_tenant', $otherTenant);

    $otherProgram = LoyaltyProgram::forCurrentTenant();
    expect($otherProgram)->toBeNull();
});
