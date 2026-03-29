<?php

namespace App\Filament\Pages;

use App\Enums\ThemeTemplate;
use App\Models\StoreTemplate;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\URL;

class ManageThemePicker extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Apariencia';

    protected static ?string $title = 'Plantillas';

    protected static ?string $navigationLabel = 'Plantillas';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.manage-theme-picker';

    public ?string $selectedThemeTemplate = null;

    public ?int $selectedStoreTemplateId = null;

    public string $activeCategory = 'all';

    public bool $showPreviewModal = false;

    public ?string $previewUrl = null;

    public string $previewDevice = 'desktop';

    public function mount(): void
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        $this->selectedThemeTemplate = $tenant?->theme_template?->value ?? 'default';
        $this->selectedStoreTemplateId = $tenant?->store_template_id;
    }

    public function selectTheme(string $value): void
    {
        $this->selectedThemeTemplate = $value;
        $this->selectedStoreTemplateId = null;
    }

    public function selectStoreTemplate(int $id): void
    {
        $this->selectedStoreTemplateId = $id;
        $this->selectedThemeTemplate = null;
    }

    public function openPreview(): void
    {
        // For StoreTemplates with demo HTML, show the real template
        if ($this->selectedStoreTemplateId) {
            $template = StoreTemplate::find($this->selectedStoreTemplateId);
            if ($template?->demo_url) {
                $this->previewUrl = $template->demo_url;
                $this->showPreviewModal = true;

                return;
            }
        }

        // For ThemeTemplate base styles, use signed storefront preview
        $params = [];

        if ($this->selectedThemeTemplate) {
            $params['theme_template'] = $this->selectedThemeTemplate;
        }

        if ($this->selectedStoreTemplateId) {
            $params['store_template_id'] = $this->selectedStoreTemplateId;
        }

        $this->previewUrl = URL::temporarySignedRoute(
            'storefront.preview',
            now()->addMinutes(30),
            $params
        );

        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewUrl = null;
    }

    public function setPreviewDevice(string $device): void
    {
        $this->previewDevice = $device;
    }

    public function applyTemplate(): void
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            Notification::make()->title('Error: no se encontró el tenant.')->danger()->send();

            return;
        }

        $tenant->update([
            'theme_template' => $this->selectedThemeTemplate ?? 'default',
            'store_template_id' => $this->selectedStoreTemplateId,
        ]);

        $this->showPreviewModal = false;

        Notification::make()
            ->title('Plantilla aplicada exitosamente')
            ->body('Los cambios ya son visibles en tu tienda.')
            ->success()
            ->send();
    }

    public function getBaseThemesProperty(): array
    {
        return collect(ThemeTemplate::cases())->map(fn (ThemeTemplate $t) => [
            'type' => 'theme',
            'key' => $t->value,
            'name' => $t->label(),
            'description' => $t->description(),
            'category' => 'base',
            'category_label' => 'Estilo Base',
            'swatches' => $t->colorSwatches(),
            'is_premium' => false,
            'features' => [],
            'fonts' => [],
        ])->toArray();
    }

    public function getStoreTemplatesProperty(): array
    {
        return StoreTemplate::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (StoreTemplate $t) => [
                'type' => 'store',
                'key' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'category' => $t->category,
                'category_label' => $t->category_label,
                'preview_image' => $t->preview_image ? asset('storage/'.$t->preview_image) : null,
                'swatches' => $t->color_swatches,
                'is_premium' => $t->is_premium,
                'features' => array_slice($t->features ?? [], 0, 3),
                'fonts' => $t->fonts ?? [],
            ])->toArray();
    }

    public function getCurrentTemplateName(): string
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if ($tenant?->store_template_id) {
            return $tenant->storeTemplate?->name ?? 'Sin plantilla';
        }

        return $tenant?->theme_template?->label() ?? 'Clásico';
    }
}
