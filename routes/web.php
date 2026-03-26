<?php

use App\Http\Controllers\LegalController;
use App\Http\Controllers\SriDownloadController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Tenant Self-Registration
Route::prefix('register-store')->name('tenant-registration.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TenantRegistrationController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\TenantRegistrationController::class, 'store'])->name('store');
    Route::get('/success', [\App\Http\Controllers\TenantRegistrationController::class, 'success'])->name('success');
    Route::get('/verify/{token}', [\App\Http\Controllers\TenantRegistrationController::class, 'verify'])->name('verify');
    Route::get('/verified', [\App\Http\Controllers\TenantRegistrationController::class, 'verified'])->name('verified');
});

Route::get('/newsletter/unsubscribe', [\App\Http\Controllers\NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

Route::get('/sitemap.xml', function () {
    $products = \App\Models\Product::where('is_active', true)->select('slug', 'updated_at')->get();
    $categories = \App\Models\Category::where('is_active', true)->select('slug', 'updated_at')->get();

    return response()->view('sitemap', compact('products', 'categories'))
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/product/{product}/barcode', function (\App\Models\Product $product) {
    return view('products.barcode', compact('product'));
})->name('product.barcode');

Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/brands', [\App\Http\Controllers\BrandPageController::class, 'index'])->name('brands.index');

Route::get('/shop', [\App\Http\Controllers\ShopController::class, 'index'])->name('shop.index');
Route::get('/about', function () {
    return view('about');
})->name('about');
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
    Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');
    Route::get('/acceptable-use', [LegalController::class, 'acceptableUse'])->name('acceptable-use');
});
Route::get('/politicas/{slug}', [\App\Http\Controllers\StorePolicyController::class, 'show'])->name('store-policy.show');
Route::get('/pagina/{slug}', [\App\Http\Controllers\PageController::class, 'show'])->name('page.show');
Route::get('/products/{product:slug}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

// Social Login Routes
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\SocialLoginController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\SocialLoginController::class, 'callback'])->name('social.callback');

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/order/{order}/shipping-label', function (\App\Models\Order $order) {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        return view('orders.shipping-label', compact('order'));
    })->name('order.shipping-label');

    Route::get('/order/{order}/invoice', function (\App\Models\Order $order) {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        return view('orders.invoice', compact('order'));
    })->name('order.invoice');
});
// });

Route::prefix('admin')
    ->middleware(['web', 'auth', 'resolve.tenant'])
    ->name('admin.')
    ->group(function () {
        Route::get('/orders/{order}/sri-xml', [SriDownloadController::class, 'downloadXml'])
            ->name('orders.sri-xml');
        Route::get('/orders/{order}/ride', [SriDownloadController::class, 'downloadRide'])
            ->name('orders.ride');
    });

Route::get('/admin/upload-logos', \App\Livewire\LogoUploader::class)
    ->middleware(['web', 'auth', 'resolve.tenant'])
    ->name('admin.upload-logos');

Route::post('/admin/upload-logo', [\App\Http\Controllers\LogoUploadController::class, 'uploadLogo'])
    ->middleware(['web', 'auth', 'resolve.tenant'])
    ->name('admin.upload-logo');

Route::post('/admin/upload-favicon', [\App\Http\Controllers\LogoUploadController::class, 'uploadFavicon'])
    ->middleware(['web', 'auth', 'resolve.tenant'])
    ->name('admin.upload-favicon');

Route::get('/checkout/shipping-rates', [\App\Http\Controllers\CheckoutController::class, 'shippingRates'])
    ->middleware(['web'])
    ->name('checkout.shipping-rates');

Route::post('/checkout/place-order', [\App\Http\Controllers\CheckoutController::class, 'placeOrder'])
    ->middleware(['web', 'auth', 'throttle:checkout'])
    ->name('checkout.place-order');

Route::get('/checkout/confirmation/{order}', [\App\Http\Controllers\CheckoutController::class, 'confirmation'])
    ->middleware(['web', 'auth'])
    ->name('checkout.confirmation');

Route::prefix('nuvei')->name('nuvei.')->middleware(['auth'])->group(function () {
    Route::get('/success/{order}', [\App\Http\Controllers\NuveiController::class, 'success'])->name('success');
    Route::get('/cancel/{order}', [\App\Http\Controllers\NuveiController::class, 'cancel'])->name('cancel');
});

Route::prefix('payphone')->name('payphone.')->middleware(['auth'])->group(function () {
    Route::get('/callback', [\App\Http\Controllers\PayPhoneController::class, 'callback'])->name('callback');
    Route::get('/cancel/{order}', [\App\Http\Controllers\PayPhoneController::class, 'cancel'])->name('cancel');
});

Route::prefix('kushki')->name('kushki.')->middleware(['auth'])->group(function () {
    Route::get('/callback', [\App\Http\Controllers\KushkiController::class, 'callback'])->name('callback');
    Route::get('/cancel/{order}', [\App\Http\Controllers\KushkiController::class, 'cancel'])->name('cancel');
});

// Account Routes
Route::middleware(['web', 'auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [\App\Http\Controllers\AccountController::class, 'orderShow'])->name('orders.show');
    Route::get('/profile', [\App\Http\Controllers\AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('password');
    Route::get('/addresses', [\App\Http\Controllers\AccountController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [\App\Http\Controllers\AccountController::class, 'storeAddress'])->name('addresses.store');
    Route::delete('/addresses/{address}', [\App\Http\Controllers\AccountController::class, 'destroyAddress'])->name('addresses.destroy');
    Route::get('/wishlist', [\App\Http\Controllers\AccountController::class, 'wishlist'])->name('wishlist');
    Route::post('/logout', [\App\Http\Controllers\AccountController::class, 'logout'])->name('logout');
});

// Notification Routes (Web)
Route::middleware(['web', 'auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
});

// Quotation Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/quotation', [\App\Http\Controllers\QuotationController::class, 'create'])->name('quotation.create');
    Route::get('/quotation/confirmation/{quotation}', [\App\Http\Controllers\QuotationController::class, 'confirmation'])->name('quotation.confirmation');
    Route::get('/my-quotations', [\App\Http\Controllers\QuotationController::class, 'myQuotations'])->name('quotations.index');
    Route::get('/my-quotations/{quotation}', [\App\Http\Controllers\QuotationController::class, 'show'])->name('quotations.show');
    Route::get('/my-quotations/{quotation}/pdf', [\App\Http\Controllers\QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
});
