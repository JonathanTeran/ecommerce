<?php

namespace App\Support;

use Illuminate\Http\Request;

class LegalAcceptance
{
    public static function snapshot(Request $request): array
    {
        return [
            'accepted_at' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'documents' => [
                'terms_of_service' => [
                    'version' => config('legal.terms.version'),
                    'accepted' => true,
                ],
                'privacy_policy' => [
                    'version' => config('legal.privacy.version'),
                    'accepted' => true,
                ],
                'acceptable_use_policy' => [
                    'version' => config('legal.acceptable_use.version'),
                    'accepted' => true,
                ],
            ],
        ];
    }

    public static function policyVersions(): array
    {
        return [
            'terms' => config('legal.terms.version'),
            'privacy' => config('legal.privacy.version'),
            'acceptable_use' => config('legal.acceptable_use.version'),
        ];
    }
}
