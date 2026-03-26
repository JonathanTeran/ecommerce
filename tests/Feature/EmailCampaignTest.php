<?php

use App\Enums\CampaignStatus;
use App\Enums\Module;
use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create(['slug' => 'email-test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise-email-'.uniqid(),
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

it('creates a campaign in draft status', function () {
    $campaign = EmailCampaign::create([
        'name' => 'Summer Sale',
        'subject' => 'Up to 50% off!',
        'content' => '<h1>Summer Sale</h1><p>Great deals await!</p>',
        'status' => CampaignStatus::Draft,
        'segment' => 'all',
        'tenant_id' => $this->tenant->id,
    ]);

    expect($campaign->status)->toBe(CampaignStatus::Draft)
        ->and($campaign->name)->toBe('Summer Sale')
        ->and((int) $campaign->sent_count)->toBe(0);
});

it('calculates open rate correctly', function () {
    $campaign = EmailCampaign::create([
        'name' => 'Test Campaign', 'subject' => 'Test',
        'content' => '<p>Test</p>',
        'status' => CampaignStatus::Sent,
        'sent_count' => 100,
        'opened_count' => 45,
        'clicked_count' => 20,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($campaign->open_rate)->toBe(45.0)
        ->and($campaign->click_rate)->toBe(20.0);
});

it('returns zero rates when no sends', function () {
    $campaign = EmailCampaign::create([
        'name' => 'Draft Campaign', 'subject' => 'Draft',
        'content' => '<p>Draft</p>',
        'status' => CampaignStatus::Draft,
        'sent_count' => 0,
        'opened_count' => 0,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($campaign->open_rate)->toBe(0.0)
        ->and($campaign->click_rate)->toBe(0.0);
});

it('scopes draft campaigns', function () {
    EmailCampaign::create([
        'name' => 'Draft', 'subject' => 'Draft', 'content' => 'x',
        'status' => CampaignStatus::Draft, 'tenant_id' => $this->tenant->id,
    ]);
    EmailCampaign::create([
        'name' => 'Sent', 'subject' => 'Sent', 'content' => 'x',
        'status' => CampaignStatus::Sent, 'tenant_id' => $this->tenant->id,
    ]);

    expect(EmailCampaign::draft()->count())->toBe(1)
        ->and(EmailCampaign::sent()->count())->toBe(1);
});

it('can schedule a campaign', function () {
    $scheduledAt = now()->addDays(3);

    $campaign = EmailCampaign::create([
        'name' => 'Scheduled', 'subject' => 'Coming Soon',
        'content' => '<p>Big news!</p>',
        'status' => CampaignStatus::Scheduled,
        'scheduled_at' => $scheduledAt,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($campaign->status)->toBe(CampaignStatus::Scheduled)
        ->and($campaign->scheduled_at->toDateString())->toBe($scheduledAt->toDateString());
});

it('resource is accessible with EmailMarketing module', function () {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect(EmailCampaignResource::canAccess())->toBeTrue();
});

it('soft deletes campaign', function () {
    $campaign = EmailCampaign::create([
        'name' => 'To Delete', 'subject' => 'Del', 'content' => 'x',
        'status' => CampaignStatus::Draft, 'tenant_id' => $this->tenant->id,
    ]);

    $campaign->delete();

    expect(EmailCampaign::count())->toBe(0)
        ->and(EmailCampaign::withTrashed()->count())->toBe(1);
});
