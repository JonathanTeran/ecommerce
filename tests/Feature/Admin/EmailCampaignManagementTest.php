<?php

use App\Enums\Module;
use App\Filament\Resources\EmailCampaignResource;
use App\Filament\Resources\EmailCampaignResource\Pages\CreateEmailCampaign;
use App\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use App\Models\EmailCampaign;
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

it('can render campaigns list page', function () {
    $this->get(EmailCampaignResource::getUrl('index'))->assertSuccessful();
});

it('can create email campaign', function () {
    Livewire::test(CreateEmailCampaign::class)
        ->fillForm([
            'name' => 'Promocion de Verano',
            'subject' => 'Descuentos de hasta 50%',
            'content' => '<p>Aprovecha nuestras ofertas de verano</p>',
            'status' => 'draft',
            'segment' => 'all',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(EmailCampaign::where('name', 'Promocion de Verano')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

it('can list campaigns in table', function () {
    $campaign = EmailCampaign::factory()->create(['tenant_id' => $this->tenant->id]);

    Livewire::test(ListEmailCampaigns::class)
        ->assertCanSeeTableRecords([$campaign]);
});

it('enforces tenant scoping', function () {
    $otherTenant = Tenant::factory()->create();
    EmailCampaign::factory()->create(['tenant_id' => $otherTenant->id]);

    expect(EmailCampaign::count())->toBe(0);
});
