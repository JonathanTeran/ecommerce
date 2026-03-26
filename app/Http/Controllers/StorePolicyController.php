<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Contracts\View\View;

class StorePolicyController extends Controller
{
    public function show(string $slug): View
    {
        $settings = GeneralSetting::cached();
        $policy = $settings?->getStorePolicyBySlug($slug);

        abort_unless($policy && ($policy['is_active'] ?? false), 404);

        return view('store-policy', [
            'policy' => $policy,
        ]);
    }
}
