<?php

use App\Models\NewsletterSubscriber;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
    $plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => ['products', 'categories', 'orders', 'storefront'],
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);
});

it('subscribes to newsletter via API', function () {
    Mail::fake();

    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'test@example.com',
    ]);

    $response->assertCreated()
        ->assertJson(['message' => 'Te has suscrito exitosamente a nuestro newsletter.']);

    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'test@example.com',
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);
});

it('validates email is required', function () {
    $response = $this->postJson('/api/newsletter/subscribe', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('validates email format', function () {
    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'not-an-email',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('reactivates existing inactive subscriber', function () {
    Mail::fake();

    $subscriber = NewsletterSubscriber::create([
        'email' => 'inactive@example.com',
        'is_active' => false,
        'source' => 'footer_form',
        'subscribed_at' => now()->subMonth(),
        'unsubscribed_at' => now()->subDay(),
    ]);

    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'inactive@example.com',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Te has suscrito nuevamente a nuestro newsletter.']);

    $subscriber->refresh();
    expect($subscriber->is_active)->toBeTrue()
        ->and($subscriber->unsubscribed_at)->toBeNull();
});

it('handles duplicate subscription gracefully', function () {
    Mail::fake();

    NewsletterSubscriber::create([
        'email' => 'existing@example.com',
        'is_active' => true,
        'source' => 'footer_form',
        'subscribed_at' => now(),
    ]);

    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'existing@example.com',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Ya estás suscrito a nuestro newsletter.']);
});

it('unsubscribes via web route with email and token', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'unsub@example.com',
        'is_active' => true,
        'source' => 'footer_form',
        'subscribed_at' => now(),
    ]);

    $token = \App\Http\Controllers\NewsletterController::generateUnsubscribeToken('unsub@example.com');

    $response = $this->get(route('newsletter.unsubscribe', [
        'email' => 'unsub@example.com',
        'token' => $token,
    ]));

    $response->assertRedirect(route('home'));

    $subscriber->refresh();
    expect($subscriber->is_active)->toBeFalse()
        ->and($subscriber->unsubscribed_at)->not->toBeNull();
});
