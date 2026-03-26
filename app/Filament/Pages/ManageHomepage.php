<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Enums\SectionType;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Support\SectionStyleHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageHomepage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Apariencia';

    protected static ?string $title = 'Diseño del Homepage';

    protected static ?string $navigationLabel = 'Homepage';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-homepage';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return false;
        }

        return $tenant->hasModule(Module::Storefront);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $sections = HomepageSection::ordered()->get();

        $this->form->fill([
            'sections' => $sections->map(fn (HomepageSection $s) => [
                'type' => $s->type->value,
                'name' => $s->name,
                'is_active' => $s->is_active,
                'config' => $s->config ?? [],
            ])->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('sections')
                    ->label('Secciones del Homepage')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipo de Sección')
                                    ->options(collect(SectionType::cases())->mapWithKeys(
                                        fn (SectionType $t) => [$t->value => $t->label()]
                                    ))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                        if ($state) {
                                            $type = SectionType::from($state);
                                            $set('name', $type->label());
                                            $set('config', $type->defaultConfig());
                                        }
                                    }),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true),
                            ]),

                        // Hero config
                        Forms\Components\Section::make('Configuración del Hero')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('config.heading')
                                        ->label('Título Principal'),
                                    Forms\Components\TextInput::make('config.badge_text')
                                        ->label('Badge / Etiqueta'),
                                ]),
                                Forms\Components\Textarea::make('config.subheading')
                                    ->label('Subtítulo')
                                    ->rows(2),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('config.cta_text')
                                        ->label('Texto Botón Principal'),
                                    Forms\Components\TextInput::make('config.cta_url')
                                        ->label('URL Botón Principal'),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('config.secondary_cta_text')
                                        ->label('Texto Botón Secundario'),
                                    Forms\Components\TextInput::make('config.secondary_cta_url')
                                        ->label('URL Botón Secundario'),
                                ]),
                                Forms\Components\FileUpload::make('config.background_image')
                                    ->label('Imagen de Fondo')
                                    ->image()
                                    ->disk('public')
                                    ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/homepage')
                                    ->visibility('public'),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::Hero->value),

                        // Category Strip config
                        Forms\Components\Section::make('Configuración de Categorías')
                            ->schema([
                                Forms\Components\Repeater::make('config.categories')
                                    ->label('Categorías (dejar vacío para mostrar las destacadas)')
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Categoría')
                                            ->options(fn () => Category::active()->ordered()->pluck('name', 'id'))
                                            ->searchable(),
                                        Forms\Components\TextInput::make('color_class')
                                            ->label('Clase de Color')
                                            ->placeholder('bg-blue-100 text-blue-600'),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Agregar Categoría'),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::CategoryStrip->value),

                        // Promo Banners config
                        Forms\Components\Section::make('Configuración de Banners')
                            ->schema([
                                Forms\Components\Repeater::make('config.banners')
                                    ->label('Banners')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('title')
                                                ->label('Título'),
                                            Forms\Components\TextInput::make('subtitle')
                                                ->label('Subtítulo'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('badge_text')
                                                ->label('Badge'),
                                            Forms\Components\Select::make('badge_color')
                                                ->label('Color del Badge')
                                                ->options([
                                                    'red' => 'Rojo',
                                                    'blue' => 'Azul',
                                                    'green' => 'Verde',
                                                    'purple' => 'Morado',
                                                    'orange' => 'Naranja',
                                                    'teal' => 'Teal',
                                                    'pink' => 'Rosa',
                                                ]),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('button_text')
                                                ->label('Texto del Botón'),
                                            Forms\Components\TextInput::make('button_url')
                                                ->label('URL del Botón'),
                                        ]),
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Imagen')
                                            ->image()
                                            ->disk('public')
                                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/homepage/banners')
                                            ->visibility('public'),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Agregar Banner')
                                    ->collapsible(),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::PromoBanners->value),

                        // CTA Strip config
                        Forms\Components\Section::make('Configuración del CTA')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('config.heading')
                                        ->label('Título'),
                                    Forms\Components\TextInput::make('config.description')
                                        ->label('Descripción'),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('config.button_text')
                                        ->label('Texto del Botón'),
                                    Forms\Components\TextInput::make('config.button_url')
                                        ->label('URL del Botón'),
                                ]),
                                Forms\Components\Select::make('config.icon')
                                    ->label('Icono')
                                    ->options([
                                        'puzzle' => 'Puzzle / Repuestos',
                                        'truck' => 'Camión / Envío',
                                        'check-circle' => 'Check / Verificado',
                                    ]),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::CtaStrip->value),

                        // Product Grid config
                        Forms\Components\Section::make('Configuración de Productos')
                            ->schema([
                                Forms\Components\TextInput::make('config.heading')
                                    ->label('Título'),
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('config.source')
                                        ->label('Fuente de Productos')
                                        ->options([
                                            'trending' => 'Tendencias (más vistos)',
                                            'featured' => 'Destacados',
                                            'new' => 'Más Recientes',
                                            'category' => 'Por Categoría',
                                        ])
                                        ->default('trending')
                                        ->live(),
                                    Forms\Components\Select::make('config.category_id')
                                        ->label('Categoría')
                                        ->options(fn () => Category::active()->ordered()->pluck('name', 'id'))
                                        ->searchable()
                                        ->visible(fn (Get $get) => $get('config.source') === 'category'),
                                    Forms\Components\TextInput::make('config.limit')
                                        ->label('Cantidad')
                                        ->numeric()
                                        ->default(8)
                                        ->minValue(1)
                                        ->maxValue(24),
                                ]),
                                Forms\Components\Toggle::make('config.enable_infinite_scroll')
                                    ->label('Scroll Infinito')
                                    ->default(true),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::ProductGrid->value),

                        // Brand Slider config
                        Forms\Components\Section::make('Configuración de Marcas')
                            ->schema([
                                Forms\Components\TextInput::make('config.heading')
                                    ->label('Título'),
                                Forms\Components\TextInput::make('config.subheading')
                                    ->label('Subtítulo'),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::BrandSlider->value),

                        // Value Props config
                        Forms\Components\Section::make('Configuración de Propuesta de Valor')
                            ->schema([
                                Forms\Components\Repeater::make('config.items')
                                    ->label('Items')
                                    ->schema([
                                        Forms\Components\Select::make('icon')
                                            ->label('Icono')
                                            ->options([
                                                'truck' => 'Camión / Envío',
                                                'check-circle' => 'Check / Verificado',
                                                'refresh' => 'Refresh / Devoluciones',
                                                'support' => 'Soporte',
                                                'shield' => 'Escudo / Seguridad',
                                                'star' => 'Estrella',
                                            ]),
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required(),
                                        Forms\Components\TextInput::make('description')
                                            ->label('Descripción'),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Agregar Item')
                                    ->collapsible(),
                            ])
                            ->collapsed()
                            ->visible(fn (Get $get) => $get('type') === SectionType::ValueProps->value),

                        // Visual Style (shared for all section types)
                        Forms\Components\Section::make('Estilo Visual')
                            ->description('Personaliza colores, espaciado y tipografía de esta sección')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('config.style.bg_type')
                                        ->label('Tipo de Fondo')
                                        ->options([
                                            'solid' => 'Color Sólido',
                                            'gradient' => 'Degradado',
                                        ])
                                        ->default('solid')
                                        ->live(),
                                    Forms\Components\Select::make('config.style.padding_preset')
                                        ->label('Espaciado Vertical')
                                        ->options([
                                            'compact' => 'Compacto (16px)',
                                            'normal' => 'Normal (48px)',
                                            'spacious' => 'Amplio (80px)',
                                            'extra' => 'Extra (128px)',
                                        ])
                                        ->default('normal'),
                                ]),

                                Forms\Components\ColorPicker::make('config.style.bg_color')
                                    ->label('Color de Fondo')
                                    ->visible(fn (Get $get) => ($get('config.style.bg_type') ?? 'solid') === 'solid'),

                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\ColorPicker::make('config.style.bg_gradient_from')
                                        ->label('Degradado Desde'),
                                    Forms\Components\ColorPicker::make('config.style.bg_gradient_to')
                                        ->label('Degradado Hasta'),
                                    Forms\Components\Select::make('config.style.bg_gradient_direction')
                                        ->label('Dirección')
                                        ->options([
                                            'to-b' => 'Arriba a Abajo',
                                            'to-r' => 'Izquierda a Derecha',
                                            'to-br' => 'Diagonal (abajo-derecha)',
                                            'to-bl' => 'Diagonal (abajo-izquierda)',
                                            'to-t' => 'Abajo a Arriba',
                                            'to-tr' => 'Diagonal (arriba-derecha)',
                                        ])
                                        ->default('to-b'),
                                ])->visible(fn (Get $get) => ($get('config.style.bg_type') ?? 'solid') === 'gradient'),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\ColorPicker::make('config.style.text_color')
                                        ->label('Color de Texto'),
                                    Forms\Components\Select::make('config.style.font_family')
                                        ->label('Tipografía')
                                        ->placeholder('Heredar del tema')
                                        ->options(SectionStyleHelper::FONT_OPTIONS),
                                ]),

                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('config.style.border_radius')
                                        ->label('Redondeo de Bordes')
                                        ->options([
                                            'none' => 'Ninguno',
                                            'small' => 'Pequeño (8px)',
                                            'medium' => 'Mediano (16px)',
                                            'large' => 'Grande (24px)',
                                            'full' => 'Completo (48px)',
                                        ])
                                        ->default('none'),
                                    Forms\Components\Toggle::make('config.style.border_top')
                                        ->label('Borde Superior')
                                        ->default(false),
                                    Forms\Components\Toggle::make('config.style.border_bottom')
                                        ->label('Borde Inferior')
                                        ->default(false),
                                ]),

                                Forms\Components\ColorPicker::make('config.style.border_color')
                                    ->label('Color del Borde')
                                    ->visible(fn (Get $get) => ($get('config.style.border_top') ?? false) || ($get('config.style.border_bottom') ?? false)),
                            ])
                            ->collapsed()
                            ->collapsible(),
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->addActionLabel('Agregar Sección')
                    ->itemLabel(fn (array $state): ?string => ($state['name'] ?? 'Nueva Sección') . (($state['is_active'] ?? true) ? '' : ' (Inactiva)')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        HomepageSection::withoutGlobalScopes()
            ->where('tenant_id', $tenant?->id)
            ->delete();

        foreach ($state['sections'] ?? [] as $index => $sectionData) {
            HomepageSection::create([
                'type' => $sectionData['type'],
                'name' => $sectionData['name'],
                'is_active' => $sectionData['is_active'] ?? true,
                'sort_order' => $index + 1,
                'config' => $sectionData['config'] ?? [],
            ]);
        }

        Notification::make()
            ->title('Homepage actualizado exitosamente')
            ->success()
            ->send();
    }
}
