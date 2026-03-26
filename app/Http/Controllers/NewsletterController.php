<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscribeRequest;
use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request): JsonResponse
    {
        $subscriber = NewsletterSubscriber::withoutGlobalScopes()
            ->where('email', $request->validated('email'))
            ->where('tenant_id', app()->bound('current_tenant') ? app('current_tenant')?->id : null)
            ->first();

        if ($subscriber) {
            if ($subscriber->is_active) {
                return response()->json([
                    'message' => 'Ya estás suscrito a nuestro newsletter.',
                ]);
            }

            $subscriber->update([
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'name' => $request->validated('name') ?? $subscriber->name,
            ]);

            return response()->json([
                'message' => 'Te has suscrito nuevamente a nuestro newsletter.',
            ]);
        }

        $subscriber = NewsletterSubscriber::create([
            'email' => $request->validated('email'),
            'name' => $request->validated('name'),
            'is_active' => true,
            'source' => 'footer_form',
            'subscribed_at' => now(),
        ]);

        Mail::send(new NewsletterWelcomeMail($subscriber));

        return response()->json([
            'message' => 'Te has suscrito exitosamente a nuestro newsletter.',
        ], 201);
    }

    public function unsubscribe(Request $request): RedirectResponse
    {
        $email = $request->query('email');
        $token = $request->query('token');

        if (! $email || ! $token) {
            return redirect()->route('home')->with('error', 'Enlace de cancelacion invalido.');
        }

        // Verify HMAC token to prevent enumeration attacks
        $expectedToken = hash_hmac('sha256', $email, config('app.key'));

        if (! hash_equals($expectedToken, $token)) {
            return redirect()->route('home')->with('error', 'Enlace de cancelacion invalido.');
        }

        $subscriber = NewsletterSubscriber::withoutGlobalScopes()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if (! $subscriber) {
            return redirect()->route('home')->with('error', 'Suscripcion no encontrada.');
        }

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Te has dado de baja del newsletter exitosamente.');
    }

    /**
     * Generate a secure unsubscribe token for an email.
     */
    public static function generateUnsubscribeToken(string $email): string
    {
        return hash_hmac('sha256', $email, config('app.key'));
    }
}
