<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['slug' => 'seo-test-store']);
    app()->instance('current_tenant', $this->tenant);
});

afterEach(function () {
    app()->forgetInstance('current_tenant');
});

function createSeoProduct(array $overrides = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'seo-cat'],
        ['name' => 'SEO Category']
    );

    return Product::create(array_merge([
        'name' => 'SEO Test Product',
        'slug' => 'seo-test-product',
        'sku' => 'SEO-001',
        'price' => 99.99,
        'cost' => 50,
        'quantity' => 10,
        'category_id' => $category->id,
        'is_active' => true,
        'description' => 'A detailed description of the SEO test product for search engines.',
        'short_description' => 'Short SEO description.',
    ], $overrides));
}

it('product page includes meta title', function () {
    $product = createSeoProduct(['meta_title' => 'Custom SEO Title']);

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('<title>Custom SEO Title</title>', false);
});

it('product page includes meta description', function () {
    $product = createSeoProduct(['meta_description' => 'Custom meta description for SEO']);

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('name="description" content="Custom meta description for SEO"', false);
});

it('product page includes open graph tags', function () {
    $product = createSeoProduct();

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('og:title', false);
    $response->assertSee('og:type" content="product"', false);
    $response->assertSee('og:url', false);
});

it('product page includes structured data', function () {
    $product = createSeoProduct();

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('application/ld+json', false);
    $response->assertSee('"@type":"Product"', false);
    $response->assertSee('"sku":"SEO-001"', false);
});

it('product page includes canonical url', function () {
    $product = createSeoProduct();

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('rel="canonical"', false);
});

it('shop page includes title', function () {
    $response = $this->get(route('shop.index'));

    $response->assertSuccessful();
    $response->assertSee('<title>Tienda</title>', false);
});

it('sitemap returns valid xml', function () {
    createSeoProduct();

    $response = $this->get('/sitemap.xml');

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('<urlset', false);
    $response->assertSee('seo-test-product', false);
});

it('product page falls back to product name when no meta title', function () {
    $product = createSeoProduct(['meta_title' => null]);

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('<title>SEO Test Product</title>', false);
});

it('product page falls back to description when no meta description', function () {
    $product = createSeoProduct(['meta_description' => null]);

    $response = $this->get(route('products.show', $product));

    $response->assertSuccessful();
    $response->assertSee('name="description" content="A detailed description', false);
});
