<?php

namespace App\Filament\Pages;

use App\Enums\Module;
use App\Models\GeneralSetting;
use App\Support\SectionStyleHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageStorefrontDesign extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Apariencia';

    protected static ?string $title = 'Diseno de Tienda';

    protected static ?string $navigationLabel = 'Diseno de Tienda';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-storefront-design';

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
        $settings = GeneralSetting::forCurrentTenant();

        $this->form->fill([
            'navbar_config' => $settings?->getNavbarConfig() ?? [],
            'footer_config' => $settings?->getFooterConfig() ?? [],
            'social_links' => $settings?->getSocialLinks() ?? [],
            'typography_config' => $settings?->getTypographyConfig() ?? [],
            'custom_css' => $settings?->custom_css ?? '',
            'product_page_config' => $settings?->getProductPageConfig() ?? [],
            'shop_page_config' => $settings?->getShopPageConfig() ?? [],
            'about_page_config' => $settings?->getAboutPageConfig() ?? [],
            'brands_page_config' => $settings?->getBrandsPageConfig() ?? [],
            'store_policies' => $settings?->getStorePolicies() ?? [],
            'domain_config' => $settings?->getDomainConfig() ?? [],
            'seo_config' => $settings?->getSeoConfig() ?? [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Storefront Design')
                    ->tabs([
                        $this->navbarTab(),
                        $this->footerTab(),
                        $this->socialTab(),
                        $this->typographyTab(),
                        $this->customCssTab(),
                        $this->productPageTab(),
                        $this->shopPageTab(),
                        $this->aboutPageTab(),
                        $this->brandsPageTab(),
                        $this->storePoliciesTab(),
                        $this->seoTab(),
                        $this->domainTab(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    private function navbarTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Navegacion')
            ->icon('heroicon-o-bars-3')
            ->schema([
                Forms\Components\Toggle::make('navbar_config.show_search')
                    ->label('Mostrar Barra de Busqueda')
                    ->default(true),
                Forms\Components\Toggle::make('navbar_config.show_categories_button')
                    ->label('Mostrar Boton PRODUCTOS')
                    ->default(true),
                Forms\Components\Select::make('navbar_config.style')
                    ->label('Estilo de Navegacion')
                    ->options([
                        'transparent_on_scroll' => 'Transparente con efecto al scroll',
                        'solid_white' => 'Fondo blanco solido',
                        'solid_dark' => 'Fondo oscuro solido',
                    ])
                    ->default('transparent_on_scroll'),
                Forms\Components\Repeater::make('navbar_config.menu_items')
                    ->label('Items del Menu')
                    ->helperText('Deja vacio para usar el menu por defecto (Home, Shop, Brands, About)')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Texto')
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->placeholder('/shop')
                            ->helperText('Usa /pagina/{slug} para enlazar paginas creadas en el constructor de paginas'),
                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('Abrir en nueva pestana')
                            ->default(false),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->addActionLabel('Agregar Item')
                    ->reorderable()
                    ->collapsible(),
            ]);
    }

    private function footerTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Pie de Pagina')
            ->icon('heroicon-o-rectangle-group')
            ->schema([
                Forms\Components\Textarea::make('footer_config.brand_description')
                    ->label('Descripcion de la Marca')
                    ->rows(3)
                    ->placeholder('Descripcion corta de tu tienda...'),
                Forms\Components\TextInput::make('footer_config.copyright_text')
                    ->label('Texto de Copyright')
                    ->placeholder('Dejar vacio para usar el texto por defecto'),
                Forms\Components\Toggle::make('footer_config.show_newsletter')
                    ->label('Mostrar Seccion de Newsletter')
                    ->default(true),
                Forms\Components\Toggle::make('footer_config.show_payment_icons')
                    ->label('Mostrar Iconos de Pago')
                    ->default(false),
                Forms\Components\Repeater::make('footer_config.columns')
                    ->label('Columnas de Links')
                    ->helperText('Deja vacio para usar las columnas por defecto (Shop, Support)')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titulo de Columna')
                            ->required(),
                        Forms\Components\Repeater::make('links')
                            ->label('Links')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Texto')
                                    ->required(),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->placeholder('/contacto'),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Link')
                            ->collapsible(),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Agregar Columna')
                    ->collapsible(),
            ]);
    }

    private function socialTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Redes Sociales')
            ->icon('heroicon-o-heart')
            ->schema([
                Forms\Components\TextInput::make('social_links.facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->placeholder('https://facebook.com/tu-pagina'),
                Forms\Components\TextInput::make('social_links.instagram_url')
                    ->label('Instagram URL')
                    ->url()
                    ->placeholder('https://instagram.com/tu-cuenta'),
                Forms\Components\TextInput::make('social_links.tiktok_url')
                    ->label('TikTok URL')
                    ->url()
                    ->placeholder('https://tiktok.com/@tu-cuenta'),
                Forms\Components\TextInput::make('social_links.twitter_url')
                    ->label('X (Twitter) URL')
                    ->url()
                    ->placeholder('https://x.com/tu-cuenta'),
                Forms\Components\TextInput::make('social_links.youtube_url')
                    ->label('YouTube URL')
                    ->url()
                    ->placeholder('https://youtube.com/@tu-canal'),
                Forms\Components\TextInput::make('social_links.whatsapp_number')
                    ->label('Numero de WhatsApp')
                    ->placeholder('+593999999999'),
            ]);
    }

    private function typographyTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Tipografia')
            ->icon('heroicon-o-language')
            ->schema([
                Forms\Components\Select::make('typography_config.body_font')
                    ->label('Fuente del Cuerpo')
                    ->options(SectionStyleHelper::FONT_OPTIONS)
                    ->placeholder('Por defecto (sistema)')
                    ->helperText('Se aplica a todo el texto del sitio'),
                Forms\Components\Select::make('typography_config.heading_font')
                    ->label('Fuente de Titulos')
                    ->options(SectionStyleHelper::FONT_OPTIONS)
                    ->placeholder('Por defecto (sistema)')
                    ->helperText('Se aplica a todos los titulos h1-h6'),
                Forms\Components\Select::make('typography_config.font_size_scale')
                    ->label('Escala de Tamano')
                    ->options([
                        'small' => 'Pequeno (14px base)',
                        'normal' => 'Normal (16px base)',
                        'large' => 'Grande (18px base)',
                    ])
                    ->default('normal'),
            ]);
    }

    private function customCssTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('CSS Personalizado')
            ->icon('heroicon-o-code-bracket')
            ->schema([
                Forms\Components\Textarea::make('custom_css')
                    ->label('CSS Personalizado')
                    ->rows(15)
                    ->placeholder('/* Tu CSS personalizado aqui */')
                    ->helperText('Maximo 10,000 caracteres. No usar <script> ni @import externos.')
                    ->maxLength(10000)
                    ->columnSpanFull(),
            ]);
    }

    private function productPageTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Pagina de Producto')
            ->icon('heroicon-o-shopping-bag')
            ->schema([
                Forms\Components\Toggle::make('product_page_config.show_related_products')
                    ->label('Mostrar Productos Relacionados')
                    ->default(true),
                Forms\Components\Toggle::make('product_page_config.show_reviews')
                    ->label('Mostrar Resenas')
                    ->default(true),
                Forms\Components\Toggle::make('product_page_config.show_specifications')
                    ->label('Mostrar Especificaciones')
                    ->default(true),
                Forms\Components\Select::make('product_page_config.related_products_count')
                    ->label('Cantidad de Productos Relacionados')
                    ->options([
                        4 => '4 productos',
                        6 => '6 productos',
                        8 => '8 productos',
                    ])
                    ->default(4),
            ]);
    }

    private function shopPageTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Pagina de Tienda')
            ->icon('heroicon-o-building-storefront')
            ->schema([
                Forms\Components\Select::make('shop_page_config.products_per_page')
                    ->label('Productos por Pagina')
                    ->options([
                        12 => '12 productos',
                        24 => '24 productos',
                        36 => '36 productos',
                    ])
                    ->default(12),
                Forms\Components\Select::make('shop_page_config.grid_columns')
                    ->label('Columnas de la Grilla')
                    ->options([
                        2 => '2 columnas',
                        3 => '3 columnas',
                        4 => '4 columnas',
                    ])
                    ->default(3),
                Forms\Components\Toggle::make('shop_page_config.show_brand_filter')
                    ->label('Mostrar Filtro de Marcas')
                    ->default(true),
                Forms\Components\Toggle::make('shop_page_config.show_category_filter')
                    ->label('Mostrar Filtro de Categorias')
                    ->default(true),
            ]);
    }

    private function aboutPageTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Pagina Acerca de')
            ->icon('heroicon-o-information-circle')
            ->schema([
                Forms\Components\TextInput::make('about_page_config.title')
                    ->label('Titulo de la Pagina')
                    ->placeholder('Nuestra Historia'),
                Forms\Components\Textarea::make('about_page_config.description')
                    ->label('Descripcion Principal')
                    ->rows(4)
                    ->placeholder('Cuenta la historia de tu tienda...'),
                Forms\Components\Repeater::make('about_page_config.values')
                    ->label('Valores / Propuesta de Valor')
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
                    ->defaultItems(0)
                    ->addActionLabel('Agregar Valor')
                    ->collapsible(),
                Forms\Components\FileUpload::make('about_page_config.team_image')
                    ->label('Imagen Principal')
                    ->image()
                    ->disk('public')
                    ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/about')
                    ->visibility('public')
                    ->helperText('Imagen para la seccion principal de la pagina About'),
                Forms\Components\TextInput::make('about_page_config.cta_text')
                    ->label('Texto del CTA sobre la Imagen')
                    ->placeholder('Construido por Creadores, para Creadores'),
            ]);
    }

    private function brandsPageTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Pagina de Marcas')
            ->icon('heroicon-o-tag')
            ->schema([
                Forms\Components\Toggle::make('brands_page_config.is_enabled')
                    ->label('Activar Pagina de Marcas')
                    ->helperText('Si se desactiva, la ruta /brands retornara 404 y no aparecera en el menu por defecto')
                    ->default(true),
                Forms\Components\TextInput::make('brands_page_config.title')
                    ->label('Titulo de la Pagina')
                    ->placeholder('Nuestras Marcas')
                    ->helperText('Dejar vacio para usar el titulo por defecto'),
                Forms\Components\Textarea::make('brands_page_config.subtitle')
                    ->label('Subtitulo / Descripcion')
                    ->rows(2)
                    ->placeholder('Explora nuestra seleccion de marcas de primera calidad...')
                    ->helperText('Dejar vacio para usar el texto por defecto'),
                Forms\Components\Select::make('brands_page_config.columns')
                    ->label('Columnas de la Grilla')
                    ->options([
                        3 => '3 columnas',
                        4 => '4 columnas',
                        6 => '6 columnas',
                    ])
                    ->default(6),
                Forms\Components\Toggle::make('brands_page_config.show_product_count')
                    ->label('Mostrar Cantidad de Productos por Marca')
                    ->default(false),
                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('brands_page_config.meta_title')
                            ->label('Meta Titulo')
                            ->placeholder('Marcas | Mi Tienda')
                            ->maxLength(70),
                        Forms\Components\Textarea::make('brands_page_config.meta_description')
                            ->label('Meta Descripcion')
                            ->rows(2)
                            ->placeholder('Descubre las mejores marcas en nuestra tienda...')
                            ->maxLength(170),
                    ])
                    ->collapsed(),
            ]);
    }

    private function storePoliciesTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Politicas de la Tienda')
            ->icon('heroicon-o-document-text')
            ->schema([
                Forms\Components\Repeater::make('store_policies')
                    ->label('Paginas de Politicas')
                    ->helperText('Crea politicas de devoluciones, envios, garantias, compras, etc. Cada una se publicara en /politicas/{slug}')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Titulo de la Politica')
                                ->required()
                                ->placeholder('Politica de Devoluciones')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Set $set, ?string $state, Forms\Get $get) {
                                    if ($state && empty($get('slug'))) {
                                        $set('slug', \Illuminate\Support\Str::slug($state));
                                    }
                                }),
                            Forms\Components\TextInput::make('slug')
                                ->label('Slug (URL)')
                                ->required()
                                ->placeholder('politica-de-devoluciones')
                                ->helperText('Se usara en la URL: /politicas/slug'),
                        ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Publicada')
                            ->default(true),
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline',
                                'h2', 'h3',
                                'bulletList', 'orderedList',
                                'link', 'blockquote',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Agregar Politica')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['title'] ?? 'Nueva Politica') . (($state['is_active'] ?? true) ? '' : ' (Borrador)')),
            ]);
    }

    private function seoTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('SEO')
            ->icon('heroicon-o-magnifying-glass')
            ->schema([
                Forms\Components\Section::make('SEO del Home')
                    ->description('Configura el titulo y descripcion que aparecen en Google cuando buscan tu tienda.')
                    ->schema([
                        Forms\Components\TextInput::make('seo_config.home_meta_title')
                            ->label('Titulo Meta del Home')
                            ->placeholder('Mi Tienda | Los mejores productos al mejor precio')
                            ->helperText('Maximo 60 caracteres. Este titulo aparece en los resultados de Google.')
                            ->maxLength(70),
                        Forms\Components\Textarea::make('seo_config.home_meta_description')
                            ->label('Meta Descripcion del Home')
                            ->placeholder('Descubre nuestra seleccion de productos premium con envio gratis...')
                            ->helperText('Maximo 160 caracteres. Describe tu tienda de forma atractiva.')
                            ->maxLength(170)
                            ->rows(3),
                        Forms\Components\TextInput::make('seo_config.home_meta_keywords')
                            ->label('Palabras Clave')
                            ->placeholder('tienda online, productos, envio gratis, ecuador')
                            ->helperText('Separadas por coma. Ejemplo: muebles, decoracion, hogar'),
                    ]),

                Forms\Components\Section::make('Datos de la Organizacion (Schema.org)')
                    ->description('Google usa estos datos para mostrar informacion de tu empresa en los resultados de busqueda (Knowledge Panel).')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('seo_config.org_name')
                                ->label('Nombre de la Empresa')
                                ->placeholder('Mi Empresa S.A.'),
                            Forms\Components\TextInput::make('seo_config.org_phone')
                                ->label('Telefono')
                                ->tel()
                                ->placeholder('+593 2 123 4567'),
                        ]),
                        Forms\Components\Textarea::make('seo_config.org_description')
                            ->label('Descripcion de la Empresa')
                            ->rows(2)
                            ->placeholder('Empresa ecuatoriana dedicada a...'),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('seo_config.org_email')
                                ->label('Email de Contacto')
                                ->email()
                                ->placeholder('info@miempresa.com'),
                            Forms\Components\TextInput::make('seo_config.org_address')
                                ->label('Direccion')
                                ->placeholder('Av. Principal N12-34'),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('seo_config.org_city')
                                ->label('Ciudad')
                                ->placeholder('Quito'),
                            Forms\Components\TextInput::make('seo_config.org_country')
                                ->label('Pais')
                                ->placeholder('Ecuador'),
                        ]),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Redes Sociales & OG Image')
                    ->description('Configuracion para compartir tu tienda en redes sociales.')
                    ->schema([
                        Forms\Components\TextInput::make('seo_config.twitter_handle')
                            ->label('Twitter/X Handle')
                            ->placeholder('@mitienda')
                            ->prefix('@'),
                        Forms\Components\FileUpload::make('seo_config.default_og_image')
                            ->label('Imagen OG por Defecto')
                            ->image()
                            ->disk('public')
                            ->directory(fn () => 'tenant-' . (app()->bound('current_tenant') ? app('current_tenant')->id : 'shared') . '/seo')
                            ->visibility('public')
                            ->helperText('Imagen que se muestra al compartir links de tu tienda (recomendado: 1200x630px)'),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Analytics y Tracking')
                    ->description('Conecta herramientas de analisis para medir el rendimiento de tu tienda.')
                    ->schema([
                        Forms\Components\TextInput::make('seo_config.google_analytics_id')
                            ->label('Google Analytics ID')
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText('ID de medicion de Google Analytics 4'),
                        Forms\Components\TextInput::make('seo_config.google_tag_manager_id')
                            ->label('Google Tag Manager ID')
                            ->placeholder('GTM-XXXXXXX'),
                        Forms\Components\TextInput::make('seo_config.facebook_pixel_id')
                            ->label('Facebook Pixel ID')
                            ->placeholder('123456789012345'),
                        Forms\Components\TextInput::make('seo_config.tiktok_pixel_id')
                            ->label('TikTok Pixel ID')
                            ->placeholder('XXXXXXXXXXXXXXXXX')
                            ->helperText('ID del pixel de TikTok (se encuentra en TikTok Ads Manager > Assets > Events)'),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Eventos de Conversion (dataLayer)')
                    ->description('Documentacion de como funcionan los eventos de seguimiento en tu tienda.')
                    ->schema([
                        Forms\Components\Placeholder::make('datalayer_docs')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="prose prose-sm dark:prose-invert max-w-none">
                                    <h4>Como funciona el seguimiento de conversiones</h4>
                                    <p>Tu tienda envia automaticamente eventos de ecommerce al <strong>dataLayer</strong> de Google Tag Manager,
                                    al <strong>Facebook Pixel</strong> y al <strong>TikTok Pixel</strong> (si estan configurados).</p>

                                    <h4>Eventos que se envian automaticamente</h4>
                                    <table>
                                        <thead><tr><th>Evento</th><th>Cuando se dispara</th><th>Datos enviados</th></tr></thead>
                                        <tbody>
                                            <tr>
                                                <td><code>view_item</code></td>
                                                <td>Al ver la pagina de un producto</td>
                                                <td>Nombre, precio, SKU, categoria, marca</td>
                                            </tr>
                                            <tr>
                                                <td><code>add_to_cart</code></td>
                                                <td>Al agregar un producto al carrito</td>
                                                <td>Producto, cantidad, valor</td>
                                            </tr>
                                            <tr>
                                                <td><code>remove_from_cart</code></td>
                                                <td>Al eliminar un producto del carrito</td>
                                                <td>Producto eliminado</td>
                                            </tr>
                                            <tr>
                                                <td><code>begin_checkout</code></td>
                                                <td>Al entrar a la pagina de checkout</td>
                                                <td>Items del carrito, valor total</td>
                                            </tr>
                                            <tr>
                                                <td><code>purchase</code></td>
                                                <td>Al completar la compra</td>
                                                <td>Order ID, total, items, metodo de pago</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <h4>Google Tag Manager (dataLayer)</h4>
                                    <p>Los eventos se envian via <code>window.dataLayer.push()</code> siguiendo el estandar
                                    <strong>GA4 Ecommerce</strong>. En GTM puedes crear triggers basados en estos eventos para:</p>
                                    <ul>
                                        <li>Enviar conversiones a Google Ads</li>
                                        <li>Crear audiencias de remarketing</li>
                                        <li>Medir el embudo de conversion</li>
                                    </ul>
                                    <p><strong>Configuracion en GTM:</strong> Crea un Trigger de tipo "Custom Event" con el nombre del evento
                                    (ej: <code>add_to_cart</code>), luego crea un Tag que use ese trigger.</p>

                                    <h4>Facebook Pixel</h4>
                                    <p>Los eventos se mapean automaticamente a los eventos estandar de Facebook:</p>
                                    <ul>
                                        <li><code>ViewContent</code> → al ver un producto</li>
                                        <li><code>AddToCart</code> → al agregar al carrito</li>
                                        <li><code>InitiateCheckout</code> → al iniciar checkout</li>
                                        <li><code>Purchase</code> → al completar compra</li>
                                    </ul>

                                    <h4>TikTok Pixel</h4>
                                    <p>Los eventos se mapean a los eventos estandar de TikTok:</p>
                                    <ul>
                                        <li><code>ViewContent</code> → al ver un producto</li>
                                        <li><code>AddToCart</code> → al agregar al carrito</li>
                                        <li><code>InitiateCheckout</code> → al iniciar checkout</li>
                                        <li><code>CompletePayment</code> → al completar compra</li>
                                    </ul>
                                </div>
                            ')),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Verificacion de Propiedad')
                    ->description('Codigos de verificacion para Google Search Console, Bing, etc.')
                    ->schema([
                        Forms\Components\TextInput::make('seo_config.google_site_verification')
                            ->label('Google Search Console')
                            ->placeholder('Codigo de verificacion de Google'),
                        Forms\Components\TextInput::make('seo_config.bing_site_verification')
                            ->label('Bing Webmaster Tools')
                            ->placeholder('Codigo de verificacion de Bing'),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Directivas de Indexacion')
                    ->schema([
                        Forms\Components\Toggle::make('seo_config.robots_index')
                            ->label('Permitir que Google indexe el sitio')
                            ->helperText('Desactiva si no quieres que tu tienda aparezca en buscadores (ej: sitio en construccion)')
                            ->default(true),
                        Forms\Components\Toggle::make('seo_config.robots_follow')
                            ->label('Permitir que Google siga los links')
                            ->default(true),
                    ])
                    ->collapsed(),
            ]);
    }

    private function domainTab(): Forms\Components\Tabs\Tab
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        return Forms\Components\Tabs\Tab::make('Dominio')
            ->icon('heroicon-o-globe-alt')
            ->schema([
                Forms\Components\Section::make('Dominio Personalizado')
                    ->description('Configura un dominio propio para tu tienda. Despues de configurar el dominio aqui, debes apuntar tu DNS (registro CNAME o A) al servidor de la plataforma.')
                    ->schema([
                        Forms\Components\TextInput::make('domain_config.custom_domain')
                            ->label('Dominio Personalizado')
                            ->placeholder('www.mitienda.com')
                            ->helperText('Ejemplo: tienda.midominio.com o www.mitienda.com'),
                        Forms\Components\Placeholder::make('current_domain')
                            ->label('Dominio Actual')
                            ->content(fn () => $tenant?->domain ?: ($tenant?->slug . '.' . request()->getHost())),
                        Forms\Components\Placeholder::make('dns_instructions')
                            ->label('Instrucciones DNS')
                            ->content('Para activar tu dominio personalizado: 1) Agrega un registro CNAME apuntando a ' . request()->getHost() . ' 2) Guarda los cambios y espera la propagacion DNS (puede tomar hasta 48 horas). 3) Contacta al soporte para activar el SSL.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $settings = GeneralSetting::forCurrentTenant();

        if (! $settings) {
            return;
        }

        $settings->update([
            'navbar_config' => $state['navbar_config'] ?? null,
            'footer_config' => $state['footer_config'] ?? null,
            'social_links' => $state['social_links'] ?? null,
            'typography_config' => $state['typography_config'] ?? null,
            'custom_css' => $state['custom_css'] ?? null,
            'product_page_config' => $state['product_page_config'] ?? null,
            'shop_page_config' => $state['shop_page_config'] ?? null,
            'about_page_config' => $state['about_page_config'] ?? null,
            'brands_page_config' => $state['brands_page_config'] ?? null,
            'store_policies' => $state['store_policies'] ?? null,
            'domain_config' => $state['domain_config'] ?? null,
            'seo_config' => $state['seo_config'] ?? null,
        ]);

        // Sync custom domain to tenant if changed
        $customDomain = $state['domain_config']['custom_domain'] ?? null;
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if ($tenant && $customDomain !== ($tenant->domain ?? '')) {
            $tenant->update(['domain' => $customDomain ?: null]);
        }

        Notification::make()
            ->title('Diseno de tienda actualizado')
            ->success()
            ->send();
    }
}
