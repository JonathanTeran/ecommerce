<?php

use App\Enums\Module;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTicketResource;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create(['slug' => 'ticket-test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-tkt-'.uniqid(),
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

it('generates ticket number automatically', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id,
        'subject' => 'Help with my order',
        'status' => TicketStatus::Open,
        'priority' => TicketPriority::Medium,
        'category' => 'order',
        'tenant_id' => $this->tenant->id,
    ]);

    expect($ticket->ticket_number)->toStartWith('TKT-');
});

it('creates a ticket with messages', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id,
        'subject' => 'Product question',
        'status' => TicketStatus::Open,
        'priority' => TicketPriority::Low,
        'category' => 'product',
        'tenant_id' => $this->tenant->id,
    ]);

    // Customer message
    $ticket->messages()->create([
        'user_id' => $user->id,
        'message' => 'Is this product available in blue?',
        'is_from_admin' => false,
    ]);

    // Admin reply
    $ticket->messages()->create([
        'user_id' => $admin->id,
        'message' => 'Yes, we have it in blue! Let me check stock.',
        'is_from_admin' => true,
    ]);

    expect($ticket->messages)->toHaveCount(2)
        ->and($ticket->messages->first()->is_from_admin)->toBeFalse()
        ->and($ticket->messages->last()->is_from_admin)->toBeTrue();
});

it('associates ticket with order', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $this->tenant->id,
    ]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'subject' => 'Order issue',
        'status' => TicketStatus::Open,
        'priority' => TicketPriority::High,
        'category' => 'order',
        'tenant_id' => $this->tenant->id,
    ]);

    expect($ticket->order->id)->toBe($order->id)
        ->and($ticket->user->id)->toBe($user->id);
});

it('assigns agent to ticket', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id,
        'assigned_to' => $agent->id,
        'subject' => 'Assigned ticket',
        'status' => TicketStatus::InProgress,
        'priority' => TicketPriority::Medium,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($ticket->assignedAgent->id)->toBe($agent->id);
});

it('resolves a ticket', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id,
        'subject' => 'To resolve',
        'status' => TicketStatus::Open,
        'priority' => TicketPriority::Medium,
        'tenant_id' => $this->tenant->id,
    ]);

    $ticket->update([
        'status' => TicketStatus::Resolved,
        'resolved_at' => now(),
    ]);

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Resolved)
        ->and($ticket->resolved_at)->not->toBeNull();
});

it('closes a ticket', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id,
        'subject' => 'To close',
        'status' => TicketStatus::Resolved,
        'priority' => TicketPriority::Low,
        'resolved_at' => now(),
        'tenant_id' => $this->tenant->id,
    ]);

    $ticket->update([
        'status' => TicketStatus::Closed,
        'closed_at' => now(),
    ]);

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Closed)
        ->and($ticket->closed_at)->not->toBeNull();
});

it('scopes open tickets', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'Open',
        'status' => TicketStatus::Open, 'priority' => TicketPriority::Medium,
        'tenant_id' => $this->tenant->id,
    ]);
    SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'In Progress',
        'status' => TicketStatus::InProgress, 'priority' => TicketPriority::High,
        'tenant_id' => $this->tenant->id,
    ]);
    SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'Closed',
        'status' => TicketStatus::Closed, 'priority' => TicketPriority::Low,
        'tenant_id' => $this->tenant->id,
    ]);

    expect(SupportTicket::open()->count())->toBe(2);
});

it('scopes unassigned tickets', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);

    SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'Unassigned',
        'status' => TicketStatus::Open, 'priority' => TicketPriority::Medium,
        'tenant_id' => $this->tenant->id,
    ]);
    SupportTicket::create([
        'user_id' => $user->id, 'assigned_to' => $agent->id,
        'subject' => 'Assigned', 'status' => TicketStatus::Open,
        'priority' => TicketPriority::Medium, 'tenant_id' => $this->tenant->id,
    ]);

    expect(SupportTicket::unassigned()->count())->toBe(1);
});

it('resource is accessible with Support module', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect(SupportTicketResource::canAccess())->toBeTrue();
});

it('resource is NOT accessible without Support module', function () {
    $plan = Plan::create([
        'name' => 'Basic', 'slug' => 'basic-no-sup-'.uniqid(),
        'type' => 'basic', 'price' => 30,
        'modules' => ['products', 'categories'],
        'is_active' => true,
    ]);
    $tenant = Tenant::create(['name' => 'No Support', 'slug' => 'no-sup-'.uniqid()]);
    Subscription::create([
        'tenant_id' => $tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect(SupportTicketResource::canAccess())->toBeFalse();
});

it('soft deletes ticket', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $ticket = SupportTicket::create([
        'user_id' => $user->id, 'subject' => 'To Delete',
        'status' => TicketStatus::Closed, 'priority' => TicketPriority::Low,
        'tenant_id' => $this->tenant->id,
    ]);

    $ticket->delete();

    expect(SupportTicket::count())->toBe(0)
        ->and(SupportTicket::withTrashed()->count())->toBe(1);
});
