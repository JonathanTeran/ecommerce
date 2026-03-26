<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\GeneralSetting;
use Illuminate\View\View;

class BrandPageController extends Controller
{
    public function index(): View
    {
        $settings = GeneralSetting::cached();
        $config = $settings?->getBrandsPageConfig() ?? [];

        if (! ($config['is_enabled'] ?? true)) {
            abort(404);
        }

        $brands = Brand::where('is_active', true)
            ->when($config['show_product_count'] ?? false, fn ($q) => $q->withCount('products'))
            ->ordered()
            ->get();

        return view('brands.index', compact('brands', 'config'));
    }
}
