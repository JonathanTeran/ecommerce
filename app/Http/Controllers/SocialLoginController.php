<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'facebook', 'apple'];

    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        $this->configureSocialiteFromTenant($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        $this->configureSocialiteFromTenant($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('filament.buyer.auth.login')
                ->with('error', 'No se pudo autenticar con ' . ucfirst($provider) . '.');
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        // Find existing user by social ID
        $user = User::query()
            ->where('social_provider', $provider)
            ->where('social_id', $socialUser->getId())
            ->where('tenant_id', $tenant?->id)
            ->first();

        if (! $user) {
            // Check if user exists with same email in this tenant
            $user = User::query()
                ->where('email', $socialUser->getEmail())
                ->where('tenant_id', $tenant?->id)
                ->first();

            if ($user) {
                // Link social account to existing user
                $user->update([
                    'social_provider' => $provider,
                    'social_id' => $socialUser->getId(),
                    'social_avatar' => $socialUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuario',
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(32)),
                    'social_provider' => $provider,
                    'social_id' => $socialUser->getId(),
                    'social_avatar' => $socialUser->getAvatar(),
                    'tenant_id' => $tenant?->id,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                $user->assignRole('customer');
            }
        }

        Auth::login($user, true);

        return redirect()->intended(route('home'));
    }

    private function configureSocialiteFromTenant(string $provider): void
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return;
        }

        $settings = GeneralSetting::cached();

        if (! $settings) {
            return;
        }

        $socialConfig = $settings->getSocialLoginConfig();
        $providerConfig = $socialConfig[$provider] ?? [];

        if (! empty($providerConfig['client_id']) && ! empty($providerConfig['client_secret'])) {
            config([
                "services.{$provider}.client_id" => $providerConfig['client_id'],
                "services.{$provider}.client_secret" => $providerConfig['client_secret'],
                "services.{$provider}.redirect" => route('social.callback', $provider),
            ]);
        }
    }
}
