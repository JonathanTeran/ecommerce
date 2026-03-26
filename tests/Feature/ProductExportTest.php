<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
});

it('has related products relationship', function () {
    $category = Category::firstOrCreate(['slug' => 'exp-cat'], ['name' => 'Test Cat']);

    $product1 = Product::create([
        'name' => 'Product A', 'slug' => 'product-a-'.uniqid(),
        'sku' => 'A-'.uniqid(), 'price' => 100,
        'quantity' => 10, 'category_id' => $category->id,
    ]);

    $product2 = Product::create([
        'name' => 'Product B', 'slug' => 'product-b-'.uniqid(),
        'sku' => 'B-'.uniqid(), 'price' => 200,
        'quantity' => 20, 'category_id' => $category->id,
    ]);

    $product1->relatedProducts()->attach($product2->id);

    expect($product1->relatedProducts)->toHaveCount(1)
        ->and($product1->relatedProducts->first()->id)->toBe($product2->id);
});

it('user has returns relationship', function () {
    $user = User::factory()->create();

    expect($user->returns())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('user has support tickets relationship', function () {
    $user = User::factory()->create();

    expect($user->supportTickets())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('order has returns relationship', function () {
    $user = User::factory()->create();
    $order = \App\Models\Order::factory()->create(['user_id' => $user->id]);

    expect($order->returns())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('order has support tickets relationship', function () {
    $user = User::factory()->create();
    $order = \App\Models\Order::factory()->create(['user_id' => $user->id]);

    expect($order->supportTickets())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
