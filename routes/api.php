<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Storefront API Routes (Public)
Route::middleware('throttle:api')->group(function () {
    Route::controller(\App\Http\Controllers\Api\StorefrontApiController::class)->group(function () {
        Route::get('/search', 'search');
        Route::get('/search/suggestions', 'searchSuggestions');
        Route::get('/search/faceted', 'searchWithFacets');
        Route::get('/categories', 'categories');
        Route::get('/products', 'products');
    });

    Route::controller(\App\Http\Controllers\Api\CartController::class)->group(function () {
        Route::get('/cart', 'index');
        Route::post('/cart', 'store');
        Route::post('/cart/apply-coupon', 'applyCoupon');
        Route::delete('/cart/coupon', 'removeCoupon');
        Route::put('/cart/{itemId}', 'update');
        Route::delete('/cart/{itemId}', 'destroy');
    });

    Route::controller(\App\Http\Controllers\Api\RecommendationApiController::class)->group(function () {
        Route::get('/products/trending', 'trending');
        Route::get('/products/{product}/recommendations/bought-together', 'boughtTogether');
        Route::get('/products/{product}/recommendations/also-viewed', 'alsoViewed');
    });
});

Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'placeOrder'])
    ->middleware('throttle:checkout');

Route::post('/quotation', [\App\Http\Controllers\Api\QuotationApiController::class, 'store'])
    ->middleware('throttle:quotation');

Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])
    ->middleware('throttle:6,1');

// Notification Routes (Authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
});
