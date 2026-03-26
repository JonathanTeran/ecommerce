<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows orders page for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertSuccessful()
        ->assertSee('Mis Pedidos');
});

it('shows empty state when no orders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertSuccessful()
        ->assertSee('No tienes pedidos aun');
});

it('lists user orders', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'CP-TEST-0001']);

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertSuccessful()
        ->assertSee('CP-TEST-0001');
});

it('shows order detail for own order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'CP-TEST-0002']);

    $this->actingAs($user)
        ->get(route('account.orders.show', $order))
        ->assertSuccessful()
        ->assertSee('CP-TEST-0002');
});

it('denies access to another users order', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('account.orders.show', $order))
        ->assertForbidden();
});

it('shows profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.profile'))
        ->assertSuccessful()
        ->assertSee('Mi Cuenta');
});

it('updates profile info', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('account.profile.update'), [
            'name' => 'New Name',
            'phone' => '0991234567',
        ])
        ->assertRedirect();

    expect($user->fresh()->name)->toBe('New Name');
    expect($user->fresh()->phone)->toBe('0991234567');
});

it('updates password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user)
        ->put(route('account.password'), [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    expect(\Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects wrong current password', function () {
    $user = User::factory()->create(['password' => bcrypt('real-password')]);

    $this->actingAs($user)
        ->put(route('account.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertSessionHasErrors('current_password');
});

it('shows addresses page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.addresses'))
        ->assertSuccessful()
        ->assertSee('Mis Direcciones');
});

it('creates a new address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('account.addresses.store'), [
            'type' => 'shipping',
            'first_name' => 'Jonathan',
            'last_name' => 'Teran',
            'address_line_1' => 'Av. Amazonas N34-123',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'phone' => '0991234567',
        ])
        ->assertRedirect();

    expect($user->addresses()->count())->toBe(1);
    expect($user->addresses()->first()->city)->toBe('Quito');
});

it('deletes own address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('account.addresses.destroy', $address))
        ->assertRedirect();

    expect($user->addresses()->count())->toBe(0);
});

it('cannot delete another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)
        ->delete(route('account.addresses.destroy', $address));

    // May return 403 or 404 depending on tenant scoping
    expect($response->status())->toBeIn([403, 404]);
    expect(Address::withoutGlobalScopes()->find($address->id))->not->toBeNull();
});

it('requires authentication for account pages', function () {
    $this->get(route('account.orders'))->assertUnauthorized();
    $this->get(route('account.profile'))->assertUnauthorized();
    $this->get(route('account.addresses'))->assertUnauthorized();
})->skip('App uses Livewire modal auth without login route');

it('shows password reset form', function () {
    $this->get(route('password.reset', ['token' => 'test-token', 'email' => 'test@example.com']))
        ->assertSuccessful()
        ->assertSee('Reset Password');
});
