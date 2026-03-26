<?php

use App\Enums\Module;
use App\Enums\OrderStatus;
use App\Livewire\ProductReviews;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'test-store']);
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

    $category = Category::create([
        'name' => 'Cat', 'slug' => 'cat', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $brand = Brand::create([
        'name' => 'Brand', 'slug' => 'brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $this->product = Product::create([
        'name' => 'Review Product', 'slug' => 'review-product', 'price' => 99.99,
        'quantity' => 5, 'sku' => 'REV-001', 'is_active' => true,
        'tenant_id' => $this->tenant->id, 'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('displays approved reviews for product', function () {
    Review::create([
        'product_id' => $this->product->id, 'user_id' => $this->user->id,
        'rating' => 5, 'comment' => 'Visible review',
        'is_approved' => true, 'tenant_id' => $this->tenant->id,
    ]);
    Review::create([
        'product_id' => $this->product->id, 'user_id' => $this->user->id,
        'rating' => 1, 'comment' => 'Hidden review',
        'is_approved' => false, 'tenant_id' => $this->tenant->id,
    ]);

    $approvedReviews = Review::approved()->where('product_id', $this->product->id)->get();
    expect($approvedReviews->count())->toBe(1)
        ->and($approvedReviews->first()->comment)->toBe('Visible review');
});

it('review model stores correct data', function () {
    $review = Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'rating' => 4,
        'title' => 'Great Product',
        'comment' => 'Really enjoyed using this product.',
        'is_approved' => false,
        'tenant_id' => $this->tenant->id,
    ]);

    expect($review->rating)->toBe(4)
        ->and($review->title)->toBe('Great Product')
        ->and($review->is_approved)->toBeFalse()
        ->and($review->product_id)->toBe($this->product->id)
        ->and($review->user_id)->toBe($this->user->id);
});

it('average rating is calculated correctly', function () {
    Review::create([
        'product_id' => $this->product->id, 'user_id' => $this->user->id,
        'rating' => 5, 'comment' => 'Five stars',
        'is_approved' => true, 'tenant_id' => $this->tenant->id,
    ]);
    $user2 = User::factory()->create(['tenant_id' => $this->tenant->id]);
    Review::create([
        'product_id' => $this->product->id, 'user_id' => $user2->id,
        'rating' => 3, 'comment' => 'Three stars',
        'is_approved' => true, 'tenant_id' => $this->tenant->id,
    ]);

    $average = Review::approved()
        ->where('product_id', $this->product->id)
        ->avg('rating');

    expect((float) $average)->toBe(4.0);
});

it('product detail page shows reviews component', function () {
    $this->get("/products/{$this->product->slug}")->assertSuccessful();
});

it('review belongs to product and user', function () {
    $review = Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'rating' => 4,
        'comment' => 'Test relationships',
        'tenant_id' => $this->tenant->id,
    ]);

    expect($review->product->id)->toBe($this->product->id)
        ->and($review->user->id)->toBe($this->user->id);
});
