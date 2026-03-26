<?php

use Illuminate\Support\Facades\Artisan;

it('generates swagger documentation without errors', function () {
    Artisan::call('l5-swagger:generate');

    expect(Artisan::output())->toContain('Regenerating docs');
});

it('has api-docs.json file after generation', function () {
    Artisan::call('l5-swagger:generate');

    $docsPath = storage_path('api-docs/api-docs.json');
    expect(file_exists($docsPath))->toBeTrue();

    $content = json_decode(file_get_contents($docsPath), true);
    expect($content)->toBeArray();
    expect($content['openapi'])->toStartWith('3.');
    expect($content['info']['title'])->toBe('Ecommerce SaaS API');
});

it('documents all public API endpoints', function () {
    Artisan::call('l5-swagger:generate');

    $docsPath = storage_path('api-docs/api-docs.json');
    $content = json_decode(file_get_contents($docsPath), true);

    $paths = array_keys($content['paths']);

    // Storefront endpoints
    expect($paths)->toContain('/search');
    expect($paths)->toContain('/search/suggestions');
    expect($paths)->toContain('/search/faceted');
    expect($paths)->toContain('/categories');
    expect($paths)->toContain('/products');

    // Cart endpoints
    expect($paths)->toContain('/cart');
    expect($paths)->toContain('/cart/{itemId}');
    expect($paths)->toContain('/cart/apply-coupon');
    expect($paths)->toContain('/cart/coupon');

    // Checkout
    expect($paths)->toContain('/checkout');

    // Quotation
    expect($paths)->toContain('/quotation');

    // Recommendations
    expect($paths)->toContain('/products/trending');
    expect($paths)->toContain('/products/{product}/recommendations/bought-together');
    expect($paths)->toContain('/products/{product}/recommendations/also-viewed');

    // Notifications
    expect($paths)->toContain('/notifications');
    expect($paths)->toContain('/notifications/{id}/read');
    expect($paths)->toContain('/notifications/read-all');
    expect($paths)->toContain('/notifications/unread-count');
});

it('includes sanctum security scheme', function () {
    Artisan::call('l5-swagger:generate');

    $docsPath = storage_path('api-docs/api-docs.json');
    $content = json_decode(file_get_contents($docsPath), true);

    expect($content['components']['securitySchemes'])->toHaveKey('sanctum');
    expect($content['components']['securitySchemes']['sanctum']['type'])->toBe('apiKey');
});

it('includes reusable schemas', function () {
    Artisan::call('l5-swagger:generate');

    $docsPath = storage_path('api-docs/api-docs.json');
    $content = json_decode(file_get_contents($docsPath), true);

    $schemas = array_keys($content['components']['schemas']);

    expect($schemas)->toContain('ProductSearchItem');
    expect($schemas)->toContain('ProductListItem');
    expect($schemas)->toContain('ProductRecommendation');
    expect($schemas)->toContain('CartResponse');
    expect($schemas)->toContain('CartAddRequest');
    expect($schemas)->toContain('CategoryItem');
    expect($schemas)->toContain('CheckoutRequest');
    expect($schemas)->toContain('OrderResponse');
    expect($schemas)->toContain('QuotationRequest');
    expect($schemas)->toContain('QuotationResponse');
});

it('has correct tags defined', function () {
    Artisan::call('l5-swagger:generate');

    $docsPath = storage_path('api-docs/api-docs.json');
    $content = json_decode(file_get_contents($docsPath), true);

    $tagNames = collect($content['tags'])->pluck('name')->toArray();

    expect($tagNames)->toContain('Catálogo');
    expect($tagNames)->toContain('Carrito');
    expect($tagNames)->toContain('Checkout');
    expect($tagNames)->toContain('Recomendaciones');
    expect($tagNames)->toContain('Cotizaciones');
    expect($tagNames)->toContain('Notificaciones');
});
