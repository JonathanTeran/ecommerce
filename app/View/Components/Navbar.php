<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Collection;

class Navbar extends Component
{
    public array $navConfig;
    public Collection $menuItems;
    public bool $defaultMenu;
    public string $navStyle;
    public array $socialLinks;
    public array $brandsConfig;
    public bool $brandsEnabled;

    public function __construct()
    {
        $tenantSettings = app()->bound('current_tenant') ? app('current_tenant')?->generalSettings : null;

        $this->navConfig = $tenantSettings?->getNavbarConfig() ?? ['menu_items' => [], 'show_search' => true, 'show_categories_button' => true, 'style' => 'transparent_on_scroll'];
        $this->menuItems = collect($this->navConfig['menu_items'] ?? [])->filter(fn ($item) => $item['is_visible'] ?? true);
        $this->defaultMenu = $this->menuItems->isEmpty();
        $this->navStyle = $this->navConfig['style'] ?? 'transparent_on_scroll';
        $this->socialLinks = $tenantSettings?->getSocialLinks() ?? [];
        $this->brandsConfig = $tenantSettings?->getBrandsPageConfig() ?? [];
        $this->brandsEnabled = $this->brandsConfig['is_enabled'] ?? true;
    }

    public function render(): View|Closure|string
    {
        return view('components.navbar');
    }
}
