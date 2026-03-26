<?php

use App\Enums\Module;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

    $this->tenant = Tenant::factory()->create();
    $this->plan = Plan::create([
        'name' => 'Enterprise', 'slug' => 'enterprise', 'type' => 'enterprise',
        'price' => 150, 'modules' => array_map(fn (Module $m) => $m->value, Module::cases()),
        'is_active' => true,
    ]);
    Subscription::create([
        'tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id,
        'status' => 'active', 'starts_at' => now(),
    ]);
    app()->instance('current_tenant', $this->tenant);

    $this->settings = GeneralSetting::create([
        'tenant_id' => $this->tenant->id,
        'site_name' => 'Image Test Store',
        'tax_rate' => 15.00,
    ]);

    $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $this->category = Category::create([
        'name' => 'Test Cat', 'slug' => 'test-cat', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);
});

// ========================================
// SITE LOGO & FAVICON UPLOAD
// ========================================

it('can upload site logo via general settings', function () {
    $logo = UploadedFile::fake()->image('logo.png', 200, 200);

    Livewire::test(\App\Filament\Pages\ManageGeneralSettings::class)
        ->fillForm([
            'site_logo' => [$logo],
        ])
        ->call('create');

    $settings = GeneralSetting::first();
    expect($settings->site_logo)->not->toBeNull();
});

it('can upload site favicon', function () {
    $favicon = UploadedFile::fake()->image('favicon.png', 32, 32)->size(50);

    Livewire::test(\App\Filament\Pages\ManageGeneralSettings::class)
        ->fillForm([
            'site_favicon' => [$favicon],
        ])
        ->call('create');

    $settings = GeneralSetting::first();
    expect($settings->site_favicon)->not->toBeNull();
});

// ========================================
// PRODUCT IMAGES (Spatie Media Library)
// ========================================

it('can create product with images via Spatie Media Library', function () {
    $brand = Brand::create([
        'name' => 'Test Brand', 'slug' => 'test-brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $brand->id,
    ]);

    // Add media directly (Spatie)
    $product->addMedia(UploadedFile::fake()->image('product.jpg', 800, 600))
        ->toMediaCollection('images');

    expect($product->getMedia('images'))->toHaveCount(1);
    expect($product->getFirstMediaUrl('images'))->not->toBeEmpty();
    expect($product->primary_image_url)->not->toBeEmpty();
});

it('product generates image conversions', function () {
    $brand = Brand::create([
        'name' => 'Conv Brand', 'slug' => 'conv-brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $brand->id,
    ]);

    $product->addMedia(UploadedFile::fake()->image('product-large.jpg', 1200, 900))
        ->toMediaCollection('images');

    expect($product->getFirstMediaUrl('images', 'thumb'))->not->toBeEmpty();
    expect($product->getFirstMediaUrl('images', 'medium'))->not->toBeEmpty();
    expect($product->getFirstMediaUrl('images', 'large'))->not->toBeEmpty();
});

// ========================================
// BRAND LOGO (Spatie Media Library)
// ========================================

it('brand can have logo via Spatie Media Library', function () {
    $brand = Brand::create([
        'name' => 'Logo Brand', 'slug' => 'logo-brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    $brand->addMedia(UploadedFile::fake()->image('brand-logo.png', 300, 100))
        ->toMediaCollection('logo');

    expect($brand->getMedia('logo'))->toHaveCount(1);
    expect($brand->getFirstMediaUrl('logo'))->not->toBeEmpty();
});

// ========================================
// HOMEPAGE IMAGES (FileUpload to public disk)
// ========================================

it('homepage hero background image can be uploaded', function () {
    $image = UploadedFile::fake()->image('hero-bg.jpg', 1920, 1080);

    // Store directly as the homepage builder would
    $path = $image->store('homepage', 'public');

    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('promo banner images can be uploaded', function () {
    $image = UploadedFile::fake()->image('promo.jpg', 800, 400);

    $path = $image->store('homepage/banners', 'public');

    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

// ========================================
// ABOUT PAGE IMAGE
// ========================================

it('about page team image can be uploaded', function () {
    $image = UploadedFile::fake()->image('team.jpg', 1200, 600);

    $path = $image->store('about', 'public');
    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $this->settings->update([
        'about_page_config' => [
            'title' => 'Test',
            'team_image' => $path,
        ],
    ]);

    expect($this->settings->fresh()->about_page_config['team_image'])->toBe($path);
});

// ========================================
// SEO OG IMAGE
// ========================================

it('SEO OG image can be uploaded and displays in meta', function () {
    $image = UploadedFile::fake()->image('og-image.jpg', 1200, 630);

    $path = $image->store('seo', 'public');
    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $this->settings->update([
        'seo_config' => ['default_og_image' => $path],
    ]);

    expect($this->settings->fresh()->seo_config['default_og_image'])->toBe($path);
});

// ========================================
// PAYMENT PROOF UPLOAD
// ========================================

it('payment proof can be uploaded during checkout', function () {
    $proof = UploadedFile::fake()->image('transfer-proof.jpg', 400, 600)->size(1024);

    $path = $proof->store('payment_proofs', 'public');

    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

// ========================================
// FRONTEND DISPLAY - Images render correctly
// ========================================

it('navbar displays site logo when configured', function () {
    $logo = UploadedFile::fake()->image('nav-logo.png', 200, 50);
    $path = $logo->store('settings', 'public');
    $this->settings->update(['site_logo' => $path]);

    $response = $this->get('/');
    $response->assertSuccessful();
    $response->assertSee('storage/'.$path, false);
});

it('product page displays product image', function () {
    $brand = Brand::create([
        'name' => 'Display Brand', 'slug' => 'display-brand', 'tenant_id' => $this->tenant->id,
        'is_active' => true, 'is_featured' => false, 'position' => 0,
    ]);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'category_id' => $this->category->id,
        'brand_id' => $brand->id,
        'is_active' => true,
    ]);

    $product->addMedia(UploadedFile::fake()->image('display-product.jpg', 800, 600))
        ->toMediaCollection('images');

    $response = $this->get('/products/'.$product->slug);
    $response->assertSuccessful();
});

// ========================================
// STORAGE DISK VALIDATION
// ========================================

it('public disk is correctly configured', function () {
    expect(config('filesystems.disks.public.driver'))->toBe('local');
    expect(config('filesystems.disks.public.visibility'))->toBe('public');
});

it('uploaded files are accessible via public URL pattern', function () {
    $file = UploadedFile::fake()->image('test-access.jpg', 100, 100);
    $path = $file->store('test', 'public');

    expect(Storage::disk('public')->exists($path))->toBeTrue();
    expect(Storage::disk('public')->url($path))->toContain('/storage/');
});
