<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('defines required account navigation routes', function () {
    expect(Route::has('account.orders'))->toBeTrue();
    expect(Route::has('account.profile'))->toBeTrue();
    expect(Route::has('account.wishlist'))->toBeTrue();
    expect(Route::has('account.logout'))->toBeTrue();
});

it('renders checkout page for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/checkout')
        ->assertSuccessful();
});

it('allows authenticated users to access wishlist and logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.wishlist'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->post(route('account.logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});
