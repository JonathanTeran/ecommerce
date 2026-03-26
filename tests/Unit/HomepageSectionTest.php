<?php

use App\Enums\Module;
use App\Enums\SectionType;
use App\Models\HomepageSection;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created via factory', function () {
    $section = HomepageSection::factory()->create();

    expect($section)->toBeInstanceOf(HomepageSection::class)
        ->and($section->exists)->toBeTrue();
});

it('casts type to SectionType enum', function () {
    $section = HomepageSection::factory()->hero()->create();

    expect($section->type)->toBeInstanceOf(SectionType::class)
        ->and($section->type)->toBe(SectionType::Hero);
});

it('casts config to array', function () {
    $section = HomepageSection::factory()->create([
        'config' => ['heading' => 'Test'],
    ]);

    expect($section->config)->toBeArray()
        ->and($section->config['heading'])->toBe('Test');
});

it('casts is_active to boolean', function () {
    $section = HomepageSection::factory()->create(['is_active' => 1]);

    expect($section->is_active)->toBeBool()->toBeTrue();
});

it('casts sort_order to integer', function () {
    $section = HomepageSection::factory()->create(['sort_order' => '5']);

    expect($section->sort_order)->toBeInt()->toBe(5);
});

it('active scope only returns active sections', function () {
    HomepageSection::factory()->create(['is_active' => true]);
    HomepageSection::factory()->inactive()->create();

    $active = HomepageSection::active()->get();

    expect($active)->toHaveCount(1)
        ->and($active->first()->is_active)->toBeTrue();
});

it('ordered scope sorts by sort_order ascending', function () {
    HomepageSection::factory()->create(['sort_order' => 3, 'name' => 'Third']);
    HomepageSection::factory()->create(['sort_order' => 1, 'name' => 'First']);
    HomepageSection::factory()->create(['sort_order' => 2, 'name' => 'Second']);

    $ordered = HomepageSection::ordered()->pluck('name')->toArray();

    expect($ordered)->toBe(['First', 'Second', 'Third']);
});

it('auto-assigns tenant_id from current_tenant on creating', function () {
    $tenant = Tenant::factory()->create();
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $tenant);

    $section = HomepageSection::create([
        'type' => SectionType::Hero,
        'name' => 'Test Hero',
        'sort_order' => 1,
        'config' => [],
    ]);

    expect($section->tenant_id)->toBe($tenant->id);
});
