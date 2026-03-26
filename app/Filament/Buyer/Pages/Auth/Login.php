<?php

namespace App\Filament\Buyer\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Str;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitException($exception);
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        // 1. Check for 'next' query parameter (explicit redirect)
        $next = request()->query('next');
        if ($next && Str::startsWith($next, ['/', 'http'])) {
            return new class($next) implements LoginResponse
            {
                protected $url;

                public function __construct($url)
                {
                    $this->url = $url;
                }

                public function toResponse($request)
                {
                    return redirect($this->url);
                }
            };
        }

        // 2. Custom Checkout Redirect Logic (Cart Session or User Cart)
        $shouldRedirectToCheckout = false;

        // A. Check Session Cart
        $sessionId = request()->cookie('cart_session_id');
        if ($sessionId) {
            $cart = \App\Models\Cart::forSession($sessionId)->first();
            if ($cart && $cart->items()->count() > 0) {
                $shouldRedirectToCheckout = true;
            }
        }

        // B. Check User Cart (Fall back if session cookie missing/invalid but user has cart)
        if (! $shouldRedirectToCheckout) {
            $userCart = \App\Models\Cart::forUser($user->id)->active()->first();
            if ($userCart && $userCart->items()->count() > 0) {
                $shouldRedirectToCheckout = true;
            }
        }

        if ($shouldRedirectToCheckout) {
            return new class implements LoginResponse
            {
                public function toResponse($request)
                {
                    return redirect()->route('checkout.index');
                }
            };
        }

        return app(LoginResponse::class);
    }
}
