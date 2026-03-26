<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_can_login(): void
    {
        Role::create(['name' => 'admin']);

        $tenant = Tenant::factory()->create();

        $user = \App\Models\User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'name' => 'Admin',
            'email_verified_at' => now(),
            'tenant_id' => $tenant->id,
        ]);
        $user->assignRole('admin');

        \Livewire\Livewire::test(\Filament\Pages\Auth\Login::class)
            ->fillForm([
                'email' => 'admin@test.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }
}
