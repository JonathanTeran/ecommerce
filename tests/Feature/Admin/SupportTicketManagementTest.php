<?php

use App\Enums\Module;
use App\Filament\Resources\SupportTicketResource;
use App\Filament\Resources\SupportTicketResource\Pages\EditSupportTicket;
use App\Filament\Resources\SupportTicketResource\Pages\ListSupportTickets;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SupportTicket;
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

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render tickets list page', function () {
    $this->get(SupportTicketResource::getUrl('index'))->assertSuccessful();
});

it('can list tickets in table', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(ListSupportTickets::class)
        ->assertCanSeeTableRecords([$ticket]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
    SupportTicket::factory()->create([
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
    ]);

    expect(SupportTicket::count())->toBe(0);
});

it('can edit ticket', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(EditSupportTicket::class, ['record' => $ticket->getRouteKey()])
        ->fillForm([
            'status' => 'in_progress',
            'priority' => 'high',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $ticket->refresh();
    expect($ticket->status->value)->toBe('in_progress')
        ->and($ticket->priority->value)->toBe('high');
});
