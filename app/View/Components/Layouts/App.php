<?php

namespace App\View\Components\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class App extends Component
{
    public array $seoConfig;
    public string $siteName;
    public string $currencySymbol;
    public ?string $pageTitle;
    public ?string $pageDescription;
    public string $pageCanonical;
    public ?string $pageOgImage;
    public string $robotsContent;

    public function __construct(
        public ?string $title = null,
        public ?string $metaDescription = null,
        public ?string $canonical = null,
        public ?string $ogImage = null,
        public ?string $ogTitle = null,
        public ?string $ogType = null,
        public ?array $breadcrumbs = null,
        public ?array $jsonLd = null,
    ) {
        $tenantSettings = app()->bound('current_tenant') ? app('current_tenant')?->generalSettings : null;

        $this->seoConfig = $tenantSettings?->getSeoConfig() ?? [];
        $this->siteName = $tenantSettings?->site_name ?? config('app.name', 'Laravel');
        $this->currencySymbol = $tenantSettings?->currency_symbol ?? '$';
        $this->pageTitle = $title ?? $this->siteName;
        $this->pageDescription = $metaDescription ?? ($this->seoConfig['home_meta_description'] ?? '');
        $this->pageCanonical = $canonical ?? url()->current();
        
        $defaultOgImage = !empty($this->seoConfig['default_og_image']) 
            ? url('storage/' . $this->seoConfig['default_og_image']) 
            : ($tenantSettings?->site_logo ? url('storage/' . $tenantSettings->site_logo) : null);
            
        $this->pageOgImage = $ogImage ?? $defaultOgImage;
        $robotsIndex = $this->seoConfig['robots_index'] ?? true;
        $robotsFollow = $this->seoConfig['robots_follow'] ?? true;
        $this->robotsContent = ($robotsIndex ? 'index' : 'noindex') . ', ' . ($robotsFollow ? 'follow' : 'nofollow');
    }

    public function render(): View|Closure|string
    {
        return view('components.layouts.app');
    }
}
