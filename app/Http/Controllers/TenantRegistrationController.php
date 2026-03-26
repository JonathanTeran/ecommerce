<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRegistrationRequest;
use App\Models\Plan;
use App\Models\TenantRegistration;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantRegistrationController extends Controller
{
    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('tenant-registration.create', compact('plans'));
    }

    public function store(StoreTenantRegistrationRequest $request)
    {
        $validated = $request->validated();

        $slug = Str::slug($validated['store_name']);
        $originalSlug = $slug;
        $counter = 1;
        while (
            TenantRegistration::where('slug', $slug)->exists() ||
            \App\Models\Tenant::where('slug', $slug)->exists()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $token = Str::random(64);

        $registration = TenantRegistration::create([
            'store_name' => $validated['store_name'],
            'slug' => $slug,
            'plan_id' => $validated['plan_id'],
            'owner_name' => $validated['owner_name'],
            'owner_email' => $validated['owner_email'],
            'owner_phone' => $validated['owner_phone'] ?? null,
            'password' => $validated['password'],
            'country' => $validated['country'],
            'verification_token' => $token,
        ]);

        Mail::send('emails.tenant-registration-verify', [
            'registration' => $registration,
            'verifyUrl' => route('tenant-registration.verify', ['token' => $token]),
        ], function ($message) use ($registration) {
            $message->to($registration->owner_email, $registration->owner_name)
                ->subject(__('Verify your email - ') . config('app.name'));
        });

        return redirect()->route('tenant-registration.success');
    }

    public function verify(string $token)
    {
        $registration = TenantRegistration::where('verification_token', $token)
            ->whereNull('email_verified_at')
            ->firstOrFail();

        $registration->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        return redirect()->route('tenant-registration.verified');
    }

    public function success()
    {
        return view('tenant-registration.success');
    }

    public function verified()
    {
        return view('tenant-registration.verified');
    }
}
