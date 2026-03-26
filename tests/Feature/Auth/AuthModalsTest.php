<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Modals;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthModalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_the_component()
    {
        Livewire::test(Modals::class)
            ->assertStatus(200);
    }

    public function test_users_can_login_via_modal()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(Modals::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(Modals::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_users_can_register_via_modal()
    {
        Livewire::test(Modals::class)
            ->set('name', 'New User')
            ->set('registerEmail', 'new@example.com')
            ->set('registerPassword', 'password')
            ->set('registerPasswordConfirmation', 'password')
            ->set('acceptLegalTerms', true)
            ->call('register')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);

        $user = User::query()->where('email', 'new@example.com')->first();

        $this->assertNotNull($user->legal_acceptance);
    }

    public function test_users_cannot_register_without_accepting_legal_terms()
    {
        Livewire::test(Modals::class)
            ->set('name', 'Blocked User')
            ->set('registerEmail', 'blocked@example.com')
            ->set('registerPassword', 'password')
            ->set('registerPasswordConfirmation', 'password')
            ->set('acceptLegalTerms', false)
            ->call('register')
            ->assertHasErrors(['acceptLegalTerms']);

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }
}
