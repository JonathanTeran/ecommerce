# Sistema de Tracking y DataLayer

## Resumen

El sistema de tracking permite a cada tenant configurar de forma independiente sus herramientas de analisis: Google Analytics 4, Google Tag Manager, Facebook Pixel y TikTok Pixel. Todos los eventos de ecommerce se disparan automaticamente a traves de una funcion centralizada `window.trackEcommerce()`.

## Arquitectura

```
Tenant Admin (Filament)
  └── ManageStorefrontDesign > SEO > Analytics y Tracking
        └── Guarda IDs en GeneralSetting.seo_config (JSON)

Frontend (Blade + JS)
  └── app.blade.php carga scripts segun seo_config
        └── window.__TRACKING__ (flags de capacidad)
        └── window.trackEcommerce() (dispatcher universal)
              ├── dataLayer.push() → GTM / GA4
              ├── fbq('track', ...) → Facebook Pixel
              └── ttq.track(...) → TikTok Pixel
```

## Aislamiento por Tenant

Cada tenant tiene su propio registro `GeneralSetting` con la columna JSON `seo_config`. El modelo usa el trait `BelongsToTenant` que:

1. Aplica un global scope para filtrar por `tenant_id`
2. Auto-asigna `tenant_id` al crear registros
3. Impide que un tenant vea la configuracion de otro

La resolucion en las vistas se hace via View Composer en `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
View::composer('*', function ($view) {
    $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
    $settings = $tenant?->generalSettings;
    $view->with('tenantSettings', $settings);
});
```

## Configuracion desde el Admin

**Ruta:** Panel Admin > Diseno de Tienda > SEO > Analytics y Tracking

**Campos disponibles:**

| Campo | Ejemplo | Descripcion |
|-------|---------|-------------|
| Google Analytics ID | `G-XXXXXXXXXX` | ID de medicion de GA4 |
| Google Tag Manager ID | `GTM-XXXXXXX` | Contenedor de GTM |
| Facebook Pixel ID | `123456789012345` | ID del Pixel de Facebook |
| TikTok Pixel ID | `XXXXXXXXXXXXXXXXX` | ID del Pixel de TikTok |

**Archivo:** `app/Filament/Pages/ManageStorefrontDesign.php` (metodo `seoTab()`, linea ~536)

## Scripts Cargados en el Frontend

Todos los scripts se cargan condicionalmente en `resources/views/components/layouts/app.blade.php`:

### Google Analytics 4 (GA4)
```blade
@if(!empty($seoConfig['google_analytics_id']))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoConfig['google_analytics_id'] }}"></script>
    <script>
        window.dataLayer=window.dataLayer||[];
        function gtag(){dataLayer.push(arguments);}
        gtag('js',new Date());
        gtag('config','{{ $seoConfig['google_analytics_id'] }}');
    </script>
@endif
```

### Google Tag Manager (GTM)
Se carga en `<head>` y tiene un fallback `<noscript>` en `<body>`:
```blade
@if(!empty($seoConfig['google_tag_manager_id']))
    {{-- Head --}}
    <script>
        (function(w,d,s,l,i){...})(window,document,'script','dataLayer','{{ $seoConfig['google_tag_manager_id'] }}');
    </script>

    {{-- Body (noscript fallback) --}}
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ $seoConfig['google_tag_manager_id'] }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
@endif
```

### Facebook Pixel
```blade
@if(!empty($seoConfig['facebook_pixel_id']))
    <script>
        !function(f,b,e,v,n,t,s){...}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init','{{ $seoConfig['facebook_pixel_id'] }}');
        fbq('track','PageView');
    </script>
@endif
```

### TikTok Pixel
```blade
@if(!empty($seoConfig['tiktok_pixel_id']))
    <script>
        !function(w,d,t){...}(window,document,"ttq");
        ttq.load('{{ $seoConfig['tiktok_pixel_id'] }}');
        ttq.page();
    </script>
@endif
```

## Objeto de Configuracion: `window.__TRACKING__`

Se renderiza siempre en `app.blade.php` para que el JavaScript sepa que plataformas estan activas:

```javascript
window.__TRACKING__ = {
    hasFbq: true/false,   // Facebook Pixel activo
    hasTtq: true/false,   // TikTok Pixel activo
    hasGtm: true/false,   // Google Tag Manager activo
    hasGa4: true/false,   // Google Analytics 4 activo
    currency: "USD"       // Moneda del tenant (ej: "USD", "EUR")
};
```

## Funcion Centralizada: `window.trackEcommerce()`

**Archivo:** `resources/js/app.js` (linea ~28)

Funcion universal que despacha eventos a todas las plataformas configuradas:

```javascript
window.trackEcommerce = function(eventName, data = {}) {
    const t = window.__TRACKING__ || {};
    const currency = t.currency || 'USD';

    // 1. dataLayer (GTM + GA4)
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ ecommerce: null }); // Limpia datos previos
    window.dataLayer.push({
        event: eventName,
        ecommerce: { currency, ...data }
    });

    // 2. Facebook Pixel (si esta activo)
    if (t.hasFbq && typeof fbq === 'function') {
        const fbEvent = fbEventMap[eventName]; // Mapeo de eventos
        if (fbEvent) fbq('track', fbEvent, fbData);
    }

    // 3. TikTok Pixel (si esta activo)
    if (t.hasTtq && typeof ttq !== 'undefined') {
        const ttEvent = ttEventMap[eventName]; // Mapeo de eventos
        if (ttEvent) ttq.track(ttEvent, ttData);
    }
};
```

## Eventos de Ecommerce

### Mapeo de Eventos por Plataforma

| Evento Interno | GTM / GA4 | Facebook Pixel | TikTok Pixel |
|----------------|-----------|----------------|--------------|
| `view_item` | `view_item` | `ViewContent` | `ViewContent` |
| `add_to_cart` | `add_to_cart` | `AddToCart` | `AddToCart` |
| `remove_from_cart` | `remove_from_cart` | *(no se envia)* | *(no se envia)* |
| `begin_checkout` | `begin_checkout` | `InitiateCheckout` | `InitiateCheckout` |
| `purchase` | `purchase` | `Purchase` | `CompletePayment` |

### Donde se Disparan

#### 1. `view_item` - Pagina de Producto

**Archivo:** `resources/views/products/show.blade.php` (linea ~233)

Se dispara al cargar la pagina de detalle de un producto.

```javascript
trackEcommerce('view_item', {
    value: 999.99,
    items: [{
        item_id: 1,
        item_name: "Laptop Premium",
        price: 999.99,
        item_category: "Electronica",
        item_brand: "MarcaX",
        quantity: 1
    }]
});
```

#### 2. `add_to_cart` - Agregar al Carrito

**Archivo:** `resources/js/app.js` (metodo `add()`, linea ~123)

Se dispara despues de que la API confirma que el producto se agrego al carrito.

```javascript
trackEcommerce('add_to_cart', {
    value: 49.99,           // precio * cantidad
    items: [{
        item_id: 5,
        item_name: "Camiseta",
        price: 49.99,
        quantity: 1
    }]
});
```

#### 3. `remove_from_cart` - Eliminar del Carrito

**Archivo:** `resources/js/app.js` (metodo `remove()`, linea ~185)

Se capturan los datos del item ANTES de eliminarlo, luego se dispara el evento.

```javascript
trackEcommerce('remove_from_cart', {
    value: 49.99,
    items: [{
        item_id: 5,
        item_name: "Camiseta",
        price: 49.99,
        quantity: 1
    }]
});
```

#### 4. `begin_checkout` - Pagina de Checkout

**Archivo:** `resources/views/checkout/index.blade.php` (linea ~249)

Se dispara al entrar a la pagina de checkout. Usa un intervalo para esperar que Alpine cargue los items del carrito.

```javascript
trackEcommerce('begin_checkout', {
    value: 150.00,          // total del carrito
    items: [
        { item_id: 1, item_name: "Producto A", price: 100, quantity: 1 },
        { item_id: 2, item_name: "Producto B", price: 50, quantity: 1 }
    ]
});
```

#### 5. `purchase` - Confirmacion de Orden

**Archivo:** `resources/views/checkout/confirmation.blade.php` (linea ~188)

Se dispara al cargar la pagina de confirmacion despues de una compra exitosa.

```javascript
trackEcommerce('purchase', {
    transaction_id: "ORD-00123",
    value: 200.00,
    tax: 24.78,
    shipping: 10.00,
    items: [
        { item_id: 1, item_name: "Laptop", price: 165.22, quantity: 1 }
    ]
});
```

## Estructura del DataLayer (GA4 Ecommerce)

Cada evento sigue el estandar GA4 Ecommerce. El dataLayer se limpia antes de cada push:

```javascript
// Paso 1: Limpiar datos anteriores
window.dataLayer.push({ ecommerce: null });

// Paso 2: Enviar evento
window.dataLayer.push({
    event: "purchase",
    ecommerce: {
        currency: "USD",
        transaction_id: "ORD-00123",
        value: 200.00,
        tax: 24.78,
        shipping: 10.00,
        items: [{
            item_id: 1,
            item_name: "Laptop Premium",
            price: 165.22,
            quantity: 1
        }]
    }
});
```

## Configuracion en GTM

Para capturar estos eventos en Google Tag Manager:

1. **Crear Variable:** Variable de capa de datos tipo "Data Layer Variable" para `ecommerce.items`, `ecommerce.value`, etc.
2. **Crear Trigger:** Tipo "Custom Event" con el nombre del evento (ej: `add_to_cart`, `purchase`)
3. **Crear Tag:** Tag de GA4 Event usando el trigger creado

## Almacenamiento en Base de Datos

Los IDs de tracking se guardan en la tabla `general_settings`, columna `seo_config` (JSON):

```json
{
    "google_analytics_id": "G-XXXXXXXXXX",
    "google_tag_manager_id": "GTM-XXXXXXX",
    "facebook_pixel_id": "123456789012345",
    "tiktok_pixel_id": "XXXXXXXXXXXXXXXXX",
    "home_meta_title": "Mi Tienda",
    "robots_index": true,
    "robots_follow": true
}
```

**Modelo:** `App\Models\GeneralSetting`
**Metodo:** `getSeoConfig()` — retorna la configuracion con valores por defecto

## Tests

**Archivo:** `tests/Feature/SeoConfigTest.php` — 31 tests, 112 assertions

| Categoria | Tests | Que verifican |
|-----------|-------|---------------|
| Admin Save | 2 | Guardar SEO config y tracking IDs desde Filament |
| Script Rendering | 8 | GA4, GTM, Facebook, TikTok se cargan/ocultan correctamente |
| Tracking Config | 3 | `window.__TRACKING__` flags y moneda correctos |
| DataLayer Events | 4 | `view_item`, `begin_checkout`, `purchase` con datos completos |
| Tenant Isolation | 3 | IDs aislados entre tenants (A no ve B, B no ve A, DB separado) |
| SEO Rendering | 6 | Meta tags, Schema.org, Twitter Cards, robots, canonical |
| Model Defaults | 3 | Valores por defecto cuando config es null |

Para ejecutar:

```bash
php artisan test --compact tests/Feature/SeoConfigTest.php
```

## Archivos Clave

| Archivo | Proposito |
|---------|-----------|
| `app/Models/GeneralSetting.php` | Modelo con `getSeoConfig()` y almacenamiento JSON |
| `app/Filament/Pages/ManageStorefrontDesign.php` | UI admin para configurar IDs (metodo `seoTab()`) |
| `resources/views/components/layouts/app.blade.php` | Carga condicional de scripts y `__TRACKING__` |
| `resources/js/app.js` | Funcion `trackEcommerce()` y eventos de carrito |
| `resources/views/products/show.blade.php` | Evento `view_item` |
| `resources/views/checkout/index.blade.php` | Evento `begin_checkout` |
| `resources/views/checkout/confirmation.blade.php` | Evento `purchase` |
| `tests/Feature/SeoConfigTest.php` | Tests completos del sistema |
