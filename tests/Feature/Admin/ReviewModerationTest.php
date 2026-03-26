<?php

use App\Enums\Module;
use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\Pages\EditReview;
use App\Filament\Resources\ReviewResource\Pages\ListReviews;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Review;
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

    $category = Category::create([
        'name' => 'Test', 'slug' => 'test', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $brand = Brand::create([
        'name' => 'Test', 'slug' => 'test-brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    $this->product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('can render reviews list page', function () {
    $this->get(ReviewResource::getUrl('index'))->assertSuccessful();
});

it('can list reviews in table', function () {
    $review = Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->customer->id,
        'rating' => 4,
        'title' => 'Great product',
        'comment' => 'I love this product',
        'is_approved' => false,
        'tenant_id' => $this->tenant->id,
    ]);

    Livewire::test(ListReviews::class)
        ->assertCanSeeTableRecords([$review]);
});

it('can approve review via edit', function () {
    $review = Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->customer->id,
        'rating' => 5,
        'comment' => 'Amazing',
        'is_approved' => false,
        'tenant_id' => $this->tenant->id,
    ]);

    Livewire::test(EditReview::class, ['record' => $review->getRouteKey()])
        ->fillForm(['is_approved' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($review->fresh()->is_approved)->toBeTrue();
});

it('can add admin response to review', function () {
    $review = Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->customer->id,
        'rating' => 3,
        'comment' => 'Decent',
        'is_approved' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    Livewire::test(EditReview::class, ['record' => $review->getRouteKey()])
        ->fillForm(['admin_response' => 'Gracias por tu comentario!'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($review->fresh()->admin_response)->toBe('Gracias por tu comentario!');
});

it('enforces tenant scoping on reviews', function () {
    $otherTenant = Tenant::factory()->create();
    $otherCategory = Category::create([
        'name' => 'Other', 'slug' => 'other-cat', 'tenant_id' => $otherTenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $otherBrand = Brand::create([
        'name' => 'Other', 'slug' => 'other-brand', 'tenant_id' => $otherTenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
    $otherProduct = Product::withoutGlobalScopes()->create([
        'name' => 'Other', 'slug' => 'other-product', 'price' => 10, 'quantity' => 1,
        'tenant_id' => $otherTenant->id, 'category_id' => $otherCategory->id,
        'brand_id' => $otherBrand->id,
    ]);
    $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
    Review::withoutGlobalScopes()->create([
        'product_id' => $otherProduct->id,
        'user_id' => $otherUser->id,
        'rating' => 5,
        'comment' => 'Hidden',
        'tenant_id' => $otherTenant->id,
    ]);

    expect(Review::count())->toBe(0);
});

it('validates rating between 1 and 5', function () {
    expect(fn () => Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->customer->id,
        'rating' => 5,
        'comment' => 'Valid',
        'tenant_id' => $this->tenant->id,
    ]))->not->toThrow(Exception::class);

    $review = Review::first();
    expect($review->rating)->toBe(5);
});

it('only approved reviews visible via scope', function () {
    Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->customer->id,
        'rating' => 4,
        'comment' => 'Pending',
        'is_approved' => false,
        'tenant_id' => $this->tenant->id,
    ]);
    Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->admin->id,
        'rating' => 5,
        'comment' => 'Approved',
        'is_approved' => true,
        'tenant_id' => $this->tenant->id,
    ]);

    expect(Review::approved()->count())->toBe(1)
        ->and(Review::approved()->first()->comment)->toBe('Approved');
});
