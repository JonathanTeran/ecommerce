<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('media')
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->ordered()
            ->get();

        return view('shop.categories', compact('categories'));
    }
}
