<?php

namespace App\Filament\Buyer\Pages\Auth;

use App\Mail\WelcomeUserMail;
use App\Services\TenantMailService;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;

class Register extends BaseRegister
{
    public function register(): ?RegistrationResponse
    {
        $preparedData = $this->prepareModelData($this->form->getState());

        $user = $this->getUserModel()::create($preparedData);

        event(new Registered($user));

        app(TenantMailService::class)->send(new WelcomeUserMail($user));

        Filament::auth()->login($user);

        session()->regenerate();

        // Custom Checkout Redirect Logic
        $sessionId = request()->cookie('cart_session_id');
        if ($sessionId) {
            $cart = \App\Models\Cart::forSession($sessionId)->first();
            if ($cart && $cart->items()->count() > 0) {
                return new class implements RegistrationResponse
                {
                    public function toResponse($request)
                    {
                        return redirect()->route('checkout.index');
                    }
                };
            }
        }

        return app(RegistrationResponse::class);
    }
}
