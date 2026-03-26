<?php

namespace App\Filament\Resources;

use App\Enums\Module;
use App\Enums\PageBlockType;
use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    use RequiresModule;

    protected static function requiredModule(): Module
    {
        return Module::Storefront;
    }

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'Pagina';

    protected static ?string $pluralModelLabel = 'Paginas';

    protected static ?string $navigationGroup = 'Apariencia';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Configuracion de Pagina')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Titulo')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Set $set, ?string $state, Forms\Get $get) {
                                    if ($state && empty($get('slug'))) {
                                        $set('slug', Str::slug($state));
                                    }
                                }),
                            Forms\Components\TextInput::make('slug')
                                ->label('Slug (URL)')
                                ->required()
                                ->maxLength(255)
                                ->unique(table: 'pages', column: 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                    $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

                                    if ($tenant) {
                                        return $rule->where('tenant_id', $tenant->id);
                                    }

                                    return $rule;
                                })
                                ->helperText('Se usara en la URL: /pagina/{slug}'),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Toggle::make('is_published')
                                ->label('Publicada')
                                ->default(false),
                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('Fecha de Publicacion')
                                ->helperText('Dejar vacio para publicar inmediatamente'),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('Orden')
                                ->numeric()
                                ->default(0),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('Titulo SEO')
                                ->maxLength(70)
                                ->helperText('Maximo 70 caracteres'),
                            Forms\Components\Textarea::make('meta_description')
                                ->label('Descripcion SEO')
                                ->maxLength(170)
                                ->rows(3)
                                ->helperText('Maximo 170 caracteres'),
                        ]),
                    ]),

                Forms\Components\Section::make('Contenido de la Pagina')
                    ->description('Agrega y ordena los bloques de contenido de tu pagina')
                    ->schema([
                        Forms\Components\Builder::make('content')
                            ->label('')
                            ->blocks([
                                Forms\Components\Builder\Block::make('rich_text')
                                    ->label(PageBlockType::RichText->label())
                                    ->icon(PageBlockType::RichText->icon())
                                    ->schema([
                                        Forms\Components\RichEditor::make('content')
                                            ->label('Contenido')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'underline', 'strike',
                                                'link', 'orderedList', 'bulletList',
                                                'h2', 'h3', 'blockquote',
                                                'redo', 'undo',
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Builder\Block::make('hero_banner')
                                    ->label(PageBlockType::HeroBanner->label())
                                    ->icon(PageBlockType::HeroBanner->icon())
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('heading')
                                                ->label('Titulo')
                                                ->required(),
                                            Forms\Components\TextInput::make('subheading')
                                                ->label('Subtitulo'),
                                        ]),
                                        Forms\Components\FileUpload::make('background_image')
                                            ->label('Imagen de Fondo')
                                            ->image()
                                            ->disk('public')
                                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/pages')
                                            ->visibility('public'),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('cta_text')
                                                ->label('Texto del Boton'),
                                            Forms\Components\TextInput::make('cta_url')
                                                ->label('URL del Boton'),
                                        ]),
                                    ]),

                                Forms\Components\Builder\Block::make('image_with_caption')
                                    ->label(PageBlockType::ImageWithCaption->label())
                                    ->icon(PageBlockType::ImageWithCaption->icon())
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Imagen')
                                            ->image()
                                            ->required()
                                            ->disk('public')
                                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/pages')
                                            ->visibility('public'),
                                        Forms\Components\TextInput::make('caption')
                                            ->label('Titulo / Descripcion'),
                                        Forms\Components\Select::make('alignment')
                                            ->label('Alineacion')
                                            ->options([
                                                'left' => 'Izquierda',
                                                'center' => 'Centro',
                                                'right' => 'Derecha',
                                            ])
                                            ->default('center'),
                                    ]),

                                Forms\Components\Builder\Block::make('features_grid')
                                    ->label(PageBlockType::FeaturesGrid->label())
                                    ->icon(PageBlockType::FeaturesGrid->icon())
                                    ->schema([
                                        Forms\Components\Select::make('columns')
                                            ->label('Columnas')
                                            ->options([
                                                2 => '2 columnas',
                                                3 => '3 columnas',
                                                4 => '4 columnas',
                                            ])
                                            ->default(3),
                                        Forms\Components\Repeater::make('features')
                                            ->label('Caracteristicas')
                                            ->schema([
                                                Forms\Components\Select::make('icon')
                                                    ->label('Icono')
                                                    ->options([
                                                        'check-circle' => 'Check / Calidad',
                                                        'bolt' => 'Rayo / Rapidez',
                                                        'support' => 'Soporte / Ayuda',
                                                        'truck' => 'Camion / Envio',
                                                        'shield' => 'Escudo / Seguridad',
                                                        'star' => 'Estrella / Excelencia',
                                                        'heart' => 'Corazon / Pasion',
                                                        'globe' => 'Globo / Global',
                                                    ]),
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Titulo')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Descripcion')
                                                    ->rows(2),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(1)
                                            ->addActionLabel('Agregar Caracteristica')
                                            ->collapsible(),
                                    ]),

                                Forms\Components\Builder\Block::make('faq')
                                    ->label(PageBlockType::Faq->label())
                                    ->icon(PageBlockType::Faq->icon())
                                    ->schema([
                                        Forms\Components\Repeater::make('items')
                                            ->label('Preguntas')
                                            ->schema([
                                                Forms\Components\TextInput::make('question')
                                                    ->label('Pregunta')
                                                    ->required(),
                                                Forms\Components\Textarea::make('answer')
                                                    ->label('Respuesta')
                                                    ->required()
                                                    ->rows(3),
                                            ])
                                            ->defaultItems(1)
                                            ->addActionLabel('Agregar Pregunta')
                                            ->collapsible(),
                                    ]),

                                Forms\Components\Builder\Block::make('call_to_action')
                                    ->label(PageBlockType::CallToAction->label())
                                    ->icon(PageBlockType::CallToAction->icon())
                                    ->schema([
                                        Forms\Components\TextInput::make('heading')
                                            ->label('Titulo')
                                            ->required(),
                                        Forms\Components\TextInput::make('description')
                                            ->label('Descripcion'),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('button_text')
                                                ->label('Texto del Boton'),
                                            Forms\Components\TextInput::make('button_url')
                                                ->label('URL del Boton'),
                                        ]),
                                        Forms\Components\ColorPicker::make('bg_color')
                                            ->label('Color de Fondo'),
                                    ]),

                                Forms\Components\Builder\Block::make('spacer')
                                    ->label(PageBlockType::Spacer->label())
                                    ->icon(PageBlockType::Spacer->icon())
                                    ->schema([
                                        Forms\Components\Select::make('height')
                                            ->label('Altura')
                                            ->options([
                                                'small' => 'Pequeno (32px)',
                                                'medium' => 'Mediano (64px)',
                                                'large' => 'Grande (96px)',
                                                'extra' => 'Extra (128px)',
                                            ])
                                            ->default('medium')
                                            ->required(),
                                    ]),

                                Forms\Components\Builder\Block::make('gallery')
                                    ->label(PageBlockType::Gallery->label())
                                    ->icon(PageBlockType::Gallery->icon())
                                    ->schema([
                                        Forms\Components\Select::make('columns')
                                            ->label('Columnas')
                                            ->options([
                                                2 => '2 columnas',
                                                3 => '3 columnas',
                                                4 => '4 columnas',
                                            ])
                                            ->default(3),
                                        Forms\Components\FileUpload::make('images')
                                            ->label('Imagenes')
                                            ->image()
                                            ->multiple()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/pages/gallery')
                                            ->visibility('public'),
                                    ]),

                                Forms\Components\Builder\Block::make('testimonials')
                                    ->label(PageBlockType::Testimonials->label())
                                    ->icon(PageBlockType::Testimonials->icon())
                                    ->schema([
                                        Forms\Components\Repeater::make('items')
                                            ->label('Testimonios')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre')
                                                    ->required(),
                                                Forms\Components\TextInput::make('role')
                                                    ->label('Cargo / Rol'),
                                                Forms\Components\Textarea::make('quote')
                                                    ->label('Testimonio')
                                                    ->required()
                                                    ->rows(3),
                                                Forms\Components\FileUpload::make('avatar')
                                                    ->label('Foto')
                                                    ->image()
                                                    ->avatar()
                                                    ->disk('public')
                                                    ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/pages/avatars')
                                                    ->visibility('public'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(1)
                                            ->addActionLabel('Agregar Testimonio')
                                            ->collapsible(),
                                    ]),
                            ])
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicada')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
