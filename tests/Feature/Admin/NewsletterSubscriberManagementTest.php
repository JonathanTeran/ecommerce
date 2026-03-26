<?php

use App\Enums\Module;
use App\Filament\Resources\NewsletterSubscriberResource;
use App\Filament\Resources\NewsletterSubscriberResource\Pages\CreateNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscriberResource\Pages\EditNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use App\Models\Plan;
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

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render newsletter subscribers list page', function () {
    $this->get(NewsletterSubscriberResource::getUrl('index'))->assertSuccessful();
});

it('can render create subscriber page', function () {
    $this->get(NewsletterSubscriberResource::getUrl('create'))->assertSuccessful();
});

it('can create a newsletter subscriber', function () {
    Livewire::test(CreateNewsletterSubscriber::class)
        ->fillForm([
            'email' => 'subscriber@example.com',
            'name' => 'Test Subscriber',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(NewsletterSubscriber::where('email', 'subscriber@example.com')
        ->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('can edit a newsletter subscriber', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'edit@example.com',
        'name' => 'Original Name',
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
        'subscribed_at' => now(),
    ]);

    Livewire::test(EditNewsletterSubscriber::class, ['record' => $subscriber->getRouteKey()])
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($subscriber->fresh()->name)->toBe('Updated Name');
});

it('can list subscribers in table', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'list@example.com',
        'name' => 'Listed',
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
        'subscribed_at' => now(),
    ]);

    Livewire::test(ListNewsletterSubscribers::class)
        ->assertCanSeeTableRecords([$subscriber]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherSubscriber = NewsletterSubscriber::create([
        'email' => 'other@example.com',
        'name' => 'Other',
        'is_active' => true,
        'tenant_id' => $otherTenant->id,
        'subscribed_at' => now(),
    ]);

    $visibleSubscribers = NewsletterSubscriber::all();
    expect($visibleSubscribers->contains($otherSubscriber))->toBeFalse();
});

it('can toggle subscriber active state', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'toggle@example.com',
        'name' => 'Toggle',
        'is_active' => true,
        'tenant_id' => $this->tenant->id,
        'subscribed_at' => now(),
    ]);

    Livewire::test(EditNewsletterSubscriber::class, ['record' => $subscriber->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($subscriber->fresh()->is_active)->toBeFalse();
});

it('requires email field', function () {
    Livewire::test(CreateNewsletterSubscriber::class)
        ->fillForm([
            'email' => '',
            'name' => 'No Email',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('subscriber inherits tenant_id on creation', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'auto@example.com',
        'is_active' => true,
        'subscribed_at' => now(),
    ]);

    expect($subscriber->tenant_id)->toBe($this->tenant->id);
});
