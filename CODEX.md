# 📦 CompuPiezas E-commerce - Codex Técnico

> **Versión:** 1.0.0  
> **Framework:** Laravel 11.x + Filament 3.x  
> **Fecha:** Enero 2026  
> **Propósito:** Reemplazo de tienda PrestaShop existente (compupiezas.net)

---

## 📋 Índice

1. [Stack Tecnológico](#-stack-tecnológico)
2. [Estructura del Proyecto](#-estructura-del-proyecto)
3. [Base de Datos](#-base-de-datos)
4. [Enums](#-enums)
5. [Modelos](#-modelos)
6. [Migraciones](#-migraciones)
7. [Filament Admin](#-filament-admin)
8. [API REST](#-api-rest)
9. [Integraciones](#-integraciones)
10. [Configuración Docker](#-configuración-docker)
11. [Instalación](#-instalación)

---

## 🛠 Stack Tecnológico

### Backend
| Componente | Tecnología | Versión |
|------------|------------|---------|
| Framework | Laravel | 11.x |
| Admin Panel | Filament | 3.x |
| Reactivity | Livewire | 3.x |
| API Auth | Laravel Sanctum | - |
| Queue | Laravel Horizon | - |
| Search | Laravel Scout + Meilisearch | - |
| Media | Spatie Media Library | 11.x |
| Permissions | Spatie Permission | 6.x |
| Slugs | Spatie Sluggable | 3.x |
| Activity Log | Spatie Activity Log | 4.x |
| Settings | Spatie Settings | 3.x |
| Images | Intervention Image | 3.x |

### Frontend
| Componente | Tecnología |
|------------|------------|
| Templates | Blade |
| JS Framework | Alpine.js |
| CSS | Tailwind CSS 3.x |
| Sliders | Swiper.js |

### Infraestructura
| Componente | Tecnología |
|------------|------------|
| Database | MySQL 8.0 / PostgreSQL 16 |
| Cache | Redis |
| Search Engine | Meilisearch |
| Containers | Docker + Laravel Sail |
| Web Server | Nginx |

---

## 📁 Estructura del Proyecto

```
compupiezas-ecommerce/
├── app/
│   ├── Actions/
│   │   ├── Cart/
│   │   │   ├── AddToCartAction.php
│   │   │   ├── UpdateCartItemAction.php
│   │   │   └── RemoveFromCartAction.php
│   │   ├── Checkout/
│   │   │   ├── CreateOrderAction.php
│   │   │   ├── ProcessPaymentAction.php
│   │   │   └── ValidateStockAction.php
│   │   └── Product/
│   │       ├── UpdateStockAction.php
│   │       └── CalculatePriceAction.php
│   ├── DTOs/
│   │   ├── CartItemDTO.php
│   │   ├── CheckoutDTO.php
│   │   └── AddressDTO.php
│   ├── Enums/
│   │   ├── OrderStatus.php
│   │   ├── PaymentMethod.php
│   │   └── PaymentStatus.php
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── ProductResource.php
│   │   │   ├── CategoryResource.php
│   │   │   ├── BrandResource.php
│   │   │   ├── OrderResource.php
│   │   │   ├── UserResource.php
│   │   │   ├── CouponResource.php
│   │   │   └── BannerResource.php
│   │   ├── Widgets/
│   │   │   ├── SalesOverviewWidget.php
│   │   │   ├── RecentOrdersWidget.php
│   │   │   ├── LowStockWidget.php
│   │   │   └── TopProductsWidget.php
│   │   └── Pages/
│   │       ├── Dashboard.php
│   │       └── Settings.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── CartController.php
│   │   │   │   ├── CheckoutController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   └── WishlistController.php
│   │   │   └── Web/
│   │   │       ├── HomeController.php
│   │   │       ├── ProductController.php
│   │   │       └── PageController.php
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Livewire/
│   │   ├── Cart/
│   │   │   ├── CartIcon.php
│   │   │   ├── CartPage.php
│   │   │   └── CartItem.php
│   │   ├── Catalog/
│   │   │   ├── ProductGrid.php
│   │   │   ├── ProductCard.php
│   │   │   ├── ProductFilters.php
│   │   │   └── CategoryMenu.php
│   │   ├── Checkout/
│   │   │   ├── CheckoutWizard.php
│   │   │   ├── ShippingForm.php
│   │   │   └── PaymentForm.php
│   │   └── Search/
│   │       └── SearchBar.php
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── User.php
│   │   ├── Address.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Cart.php
│   │   ├── CartItem.php
│   │   ├── Coupon.php
│   │   ├── Payment.php
│   │   ├── Review.php
│   │   ├── Wishlist.php
│   │   ├── Banner.php
│   │   └── NewsletterSubscriber.php
│   ├── Services/
│   │   ├── Cart/
│   │   │   └── CartService.php
│   │   ├── Payment/
│   │   │   ├── PaymentGatewayInterface.php
│   │   │   ├── PayPhoneGateway.php
│   │   │   ├── DatafastGateway.php
│   │   │   └── PayPalGateway.php
│   │   ├── Shipping/
│   │   │   ├── ShippingProviderInterface.php
│   │   │   └── ServientregaProvider.php
│   │   └── Inventory/
│   │       └── StockService.php
│   └── Repositories/
│       ├── ProductRepository.php
│       └── OrderRepository.php
├── config/
│   ├── compupiezas.php
│   └── payment.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/
│   ├── nginx/
│   └── php/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── components/
│       ├── layouts/
│       ├── livewire/
│       └── pages/
├── routes/
│   ├── api.php
│   └── web.php
├── storage/
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 🗄 Base de Datos

### Diagrama ER (Simplificado)

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  categories │────<│  products   │>────│   brands    │
└─────────────┘     └──────┬──────┘     └─────────────┘
       │                   │
       │                   │
       ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  (self-ref) │     │   reviews   │     │  variants   │
└─────────────┘     └─────────────┘     └─────────────┘
                           │
                           │
┌─────────────┐     ┌──────┴──────┐     ┌─────────────┐
│  addresses  │────<│    users    │>────│  wishlists  │
└─────────────┘     └──────┬──────┘     └─────────────┘
                           │
                           │
┌─────────────┐     ┌──────┴──────┐     ┌─────────────┐
│   payments  │────<│   orders    │>────│ order_items │
└─────────────┘     └──────┬──────┘     └─────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │status_history│
                    └─────────────┘

┌─────────────┐     ┌─────────────┐
│    carts    │────<│ cart_items  │
└─────────────┘     └─────────────┘

┌─────────────┐     ┌─────────────┐
│   coupons   │     │   banners   │
└─────────────┘     └─────────────┘
```

### Tablas Detalladas

#### `categories`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| parent_id | BIGINT | FK nullable (self-reference) |
| name | VARCHAR(255) | Nombre de la categoría |
| slug | VARCHAR(255) | URL amigable, único |
| description | TEXT | Descripción (nullable) |
| icon | VARCHAR(100) | Clase de ícono (nullable) |
| position | INT | Orden de visualización (default: 0) |
| is_active | BOOLEAN | Estado activo (default: true) |
| is_featured | BOOLEAN | Destacada (default: false) |
| meta_title | VARCHAR(255) | SEO title |
| meta_description | TEXT | SEO description |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `parent_id`, `slug`, `is_active`, `position`

---

#### `brands`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| name | VARCHAR(255) | Nombre de la marca |
| slug | VARCHAR(255) | URL amigable, único |
| description | TEXT | Descripción (nullable) |
| website | VARCHAR(255) | Sitio web oficial (nullable) |
| position | INT | Orden (default: 0) |
| is_active | BOOLEAN | Estado activo (default: true) |
| is_featured | BOOLEAN | Destacada (default: false) |
| meta_title | VARCHAR(255) | SEO |
| meta_description | TEXT | SEO |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `slug`, `is_active`, `position`

---

#### `products`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| category_id | BIGINT | FK → categories |
| brand_id | BIGINT | FK → brands (nullable) |
| sku | VARCHAR(100) | Código único |
| name | VARCHAR(255) | Nombre del producto |
| slug | VARCHAR(255) | URL amigable, único |
| description | TEXT | Descripción completa |
| short_description | VARCHAR(500) | Resumen |
| price | DECIMAL(10,2) | Precio de venta |
| compare_price | DECIMAL(10,2) | Precio anterior/comparación |
| cost | DECIMAL(10,2) | Costo (nullable) |
| quantity | INT | Stock disponible (default: 0) |
| low_stock_threshold | INT | Alerta stock bajo (default: 5) |
| weight | DECIMAL(8,2) | Peso en kg (nullable) |
| dimensions | JSON | {length, width, height} |
| specifications | JSON | Especificaciones técnicas |
| compatible_models | JSON | Array de modelos compatibles |
| warranty_months | INT | Garantía en meses (default: 0) |
| views_count | INT | Contador de vistas (default: 0) |
| sales_count | INT | Contador de ventas (default: 0) |
| is_active | BOOLEAN | Activo (default: true) |
| is_featured | BOOLEAN | Destacado (default: false) |
| is_new | BOOLEAN | Es nuevo (default: true) |
| requires_shipping | BOOLEAN | Requiere envío (default: true) |
| meta_title | VARCHAR(255) | SEO |
| meta_description | TEXT | SEO |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `category_id`, `brand_id`, `sku`, `slug`, `is_active`, `is_featured`, `price`, `quantity`

---

#### `users`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| name | VARCHAR(255) | Nombre completo |
| email | VARCHAR(255) | Email único |
| email_verified_at | TIMESTAMP | Verificación |
| password | VARCHAR(255) | Hash de contraseña |
| phone | VARCHAR(20) | Teléfono (nullable) |
| cedula | VARCHAR(20) | Cédula/RUC (nullable) |
| date_of_birth | DATE | Fecha nacimiento (nullable) |
| gender | ENUM | male/female/other (nullable) |
| is_active | BOOLEAN | Activo (default: true) |
| last_login_at | TIMESTAMP | Último acceso |
| last_login_ip | VARCHAR(45) | IP del último acceso |
| preferences | JSON | Preferencias del usuario |
| newsletter_subscribed | BOOLEAN | Suscrito (default: false) |
| remember_token | VARCHAR(100) | - |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `email`, `cedula`, `is_active`

---

#### `addresses`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| user_id | BIGINT | FK → users |
| type | ENUM | billing/shipping |
| label | VARCHAR(50) | Etiqueta (Casa, Oficina) |
| first_name | VARCHAR(100) | Nombre |
| last_name | VARCHAR(100) | Apellido |
| cedula | VARCHAR(20) | Cédula (nullable) |
| company | VARCHAR(255) | Empresa (nullable) |
| address_line_1 | VARCHAR(255) | Dirección principal |
| address_line_2 | VARCHAR(255) | Dirección secundaria |
| city | VARCHAR(100) | Ciudad |
| province | VARCHAR(100) | Provincia |
| postal_code | VARCHAR(20) | Código postal (nullable) |
| country | VARCHAR(2) | ISO country code (default: EC) |
| phone | VARCHAR(20) | Teléfono |
| delivery_instructions | TEXT | Instrucciones (nullable) |
| latitude | DECIMAL(10,8) | Coordenada (nullable) |
| longitude | DECIMAL(11,8) | Coordenada (nullable) |
| is_default | BOOLEAN | Dirección por defecto |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `user_id`, `type`, `is_default`

---

#### `orders`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| user_id | BIGINT | FK → users (nullable) |
| order_number | VARCHAR(20) | Número único (CP-YYMMDD-XXXX) |
| status | VARCHAR(20) | Estado del pedido |
| payment_status | VARCHAR(20) | Estado del pago |
| payment_method | VARCHAR(50) | Método de pago |
| subtotal | DECIMAL(12,2) | Subtotal |
| discount_amount | DECIMAL(12,2) | Descuento aplicado |
| coupon_code | VARCHAR(50) | Código cupón usado |
| tax_amount | DECIMAL(12,2) | IVA (15%) |
| shipping_amount | DECIMAL(12,2) | Costo envío |
| total | DECIMAL(12,2) | Total final |
| currency | VARCHAR(3) | Moneda (default: USD) |
| notes | TEXT | Notas del cliente |
| admin_notes | TEXT | Notas internas |
| billing_address | JSON | Dirección facturación |
| shipping_address | JSON | Dirección envío |
| shipping_method | VARCHAR(50) | Método de envío |
| tracking_number | VARCHAR(100) | Número de guía |
| shipped_at | TIMESTAMP | Fecha envío |
| delivered_at | TIMESTAMP | Fecha entrega |
| cancelled_at | TIMESTAMP | Fecha cancelación |
| cancellation_reason | TEXT | Motivo cancelación |
| ip_address | VARCHAR(45) | IP del cliente |
| user_agent | TEXT | Navegador |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `user_id`, `order_number`, `status`, `payment_status`, `created_at`

---

#### `order_items`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| order_id | BIGINT | FK → orders |
| product_id | BIGINT | FK → products |
| variant_id | BIGINT | FK → product_variants (nullable) |
| name | VARCHAR(255) | Nombre al momento de compra |
| sku | VARCHAR(100) | SKU al momento de compra |
| price | DECIMAL(10,2) | Precio unitario |
| quantity | INT | Cantidad |
| subtotal | DECIMAL(12,2) | Subtotal del item |
| options | JSON | Opciones seleccionadas |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `order_id`, `product_id`

---

#### `carts`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| user_id | BIGINT | FK → users (nullable) |
| session_id | VARCHAR(100) | ID de sesión |
| coupon_id | BIGINT | FK → coupons (nullable) |
| expires_at | TIMESTAMP | Expiración (30 días) |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `user_id`, `session_id`, `expires_at`

---

#### `cart_items`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| cart_id | BIGINT | FK → carts |
| product_id | BIGINT | FK → products |
| variant_id | BIGINT | FK → product_variants (nullable) |
| quantity | INT | Cantidad |
| price | DECIMAL(10,2) | Precio al agregar |
| options | JSON | Opciones seleccionadas |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `cart_id`, `product_id`

---

#### `coupons`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| code | VARCHAR(50) | Código único |
| name | VARCHAR(255) | Nombre interno |
| description | TEXT | Descripción |
| type | ENUM | fixed/percentage/free_shipping |
| value | DECIMAL(10,2) | Valor del descuento |
| min_order_amount | DECIMAL(10,2) | Mínimo de compra |
| max_discount_amount | DECIMAL(10,2) | Máximo descuento |
| usage_limit | INT | Límite total de usos |
| usage_count | INT | Usos actuales |
| usage_limit_per_user | INT | Límite por usuario |
| starts_at | TIMESTAMP | Inicio vigencia |
| expires_at | TIMESTAMP | Fin vigencia |
| is_active | BOOLEAN | Activo |
| applicable_categories | JSON | Categorías aplicables |
| applicable_products | JSON | Productos aplicables |
| excluded_products | JSON | Productos excluidos |
| first_order_only | BOOLEAN | Solo primera compra |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `code`, `is_active`, `starts_at`, `expires_at`

---

#### `payments`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| order_id | BIGINT | FK → orders |
| transaction_id | VARCHAR(255) | ID transacción gateway |
| gateway | VARCHAR(50) | Nombre del gateway |
| method | VARCHAR(50) | Método específico |
| amount | DECIMAL(12,2) | Monto pagado |
| currency | VARCHAR(3) | Moneda |
| status | VARCHAR(20) | Estado del pago |
| gateway_response | JSON | Respuesta completa |
| error_message | TEXT | Mensaje de error |
| paid_at | TIMESTAMP | Fecha de pago |
| refunded_at | TIMESTAMP | Fecha reembolso |
| refunded_amount | DECIMAL(12,2) | Monto reembolsado |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `order_id`, `transaction_id`, `status`

---

#### `reviews`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| product_id | BIGINT | FK → products |
| user_id | BIGINT | FK → users |
| order_id | BIGINT | FK → orders (nullable) |
| rating | TINYINT | 1-5 estrellas |
| title | VARCHAR(255) | Título (nullable) |
| comment | TEXT | Comentario |
| pros | TEXT | Pros (nullable) |
| cons | TEXT | Contras (nullable) |
| is_verified_purchase | BOOLEAN | Compra verificada |
| is_approved | BOOLEAN | Aprobada (default: false) |
| is_featured | BOOLEAN | Destacada |
| admin_response | TEXT | Respuesta admin |
| approved_at | TIMESTAMP | Fecha aprobación |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |
| deleted_at | TIMESTAMP | Soft delete |

**Índices:** `product_id`, `user_id`, `is_approved`, `rating`

---

#### `product_variants`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| product_id | BIGINT | FK → products |
| name | VARCHAR(255) | Nombre variante |
| sku | VARCHAR(100) | SKU único |
| price | DECIMAL(10,2) | Precio (nullable = usa precio base) |
| quantity | INT | Stock (default: 0) |
| options | JSON | {color: "Negro", capacidad: "256GB"} |
| is_active | BOOLEAN | Activa |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `product_id`, `sku`, `is_active`

---

#### `wishlists`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| user_id | BIGINT | FK → users |
| product_id | BIGINT | FK → products |
| created_at | TIMESTAMP | - |

**Índices:** `user_id`, `product_id` (unique together)

---

#### `banners`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| title | VARCHAR(255) | Título |
| subtitle | VARCHAR(255) | Subtítulo (nullable) |
| button_text | VARCHAR(50) | Texto del botón (nullable) |
| button_url | VARCHAR(255) | URL del botón |
| position | VARCHAR(50) | Ubicación (home_slider, sidebar) |
| sort_order | INT | Orden |
| is_active | BOOLEAN | Activo |
| starts_at | TIMESTAMP | Inicio (nullable) |
| expires_at | TIMESTAMP | Fin (nullable) |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `position`, `is_active`, `sort_order`

---

#### `newsletter_subscribers`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| email | VARCHAR(255) | Email único |
| name | VARCHAR(255) | Nombre (nullable) |
| is_active | BOOLEAN | Activo |
| source | VARCHAR(50) | Fuente (footer, popup, checkout) |
| subscribed_at | TIMESTAMP | Fecha suscripción |
| unsubscribed_at | TIMESTAMP | Fecha baja |
| created_at | TIMESTAMP | - |
| updated_at | TIMESTAMP | - |

**Índices:** `email`, `is_active`

---

#### `order_status_histories`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK |
| order_id | BIGINT | FK → orders |
| user_id | BIGINT | FK → users (nullable) |
| old_status | VARCHAR(20) | Estado anterior |
| new_status | VARCHAR(20) | Nuevo estado |
| notes | TEXT | Notas del cambio |
| created_at | TIMESTAMP | - |

**Índices:** `order_id`, `created_at`

---

## 🏷 Enums

### OrderStatus

```php
<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case ON_HOLD = 'on_hold';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::CONFIRMED => 'Confirmado',
            self::PROCESSING => 'En Proceso',
            self::SHIPPED => 'Enviado',
            self::DELIVERED => 'Entregado',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Reembolsado',
            self::ON_HOLD => 'En Espera',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'info',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'gray',
            self::ON_HOLD => 'warning',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::CONFIRMED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-cog',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REFUNDED => 'heroicon-o-arrow-uturn-left',
            self::ON_HOLD => 'heroicon-o-pause-circle',
        };
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        $transitions = [
            self::PENDING->value => [self::CONFIRMED, self::CANCELLED, self::ON_HOLD],
            self::CONFIRMED->value => [self::PROCESSING, self::CANCELLED, self::ON_HOLD],
            self::PROCESSING->value => [self::SHIPPED, self::CANCELLED, self::ON_HOLD],
            self::SHIPPED->value => [self::DELIVERED, self::CANCELLED],
            self::DELIVERED->value => [self::REFUNDED],
            self::ON_HOLD->value => [self::PENDING, self::CONFIRMED, self::PROCESSING, self::CANCELLED],
            self::CANCELLED->value => [],
            self::REFUNDED->value => [],
        ];

        return in_array($newStatus, $transitions[$this->value] ?? []);
    }

    public static function activeStatuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PROCESSING,
            self::SHIPPED,
            self::ON_HOLD,
        ];
    }
}
```

---

### PaymentMethod

```php
<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PAYPHONE = 'payphone';
    case PAYPAL = 'paypal';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_ON_DELIVERY = 'cash_on_delivery';

    public function label(): string
    {
        return match($this) {
            self::CREDIT_CARD => 'Tarjeta de Crédito',
            self::DEBIT_CARD => 'Tarjeta de Débito',
            self::PAYPHONE => 'PayPhone',
            self::PAYPAL => 'PayPal',
            self::BANK_TRANSFER => 'Transferencia Bancaria',
            self::CASH_ON_DELIVERY => 'Pago Contra Entrega',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::DEBIT_CARD => 'heroicon-o-credit-card',
            self::PAYPHONE => 'heroicon-o-device-phone-mobile',
            self::PAYPAL => 'heroicon-o-globe-alt',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::CASH_ON_DELIVERY => 'heroicon-o-banknotes',
        };
    }

    public function requiresOnlineProcessing(): bool
    {
        return in_array($this, [
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::PAYPHONE,
            self::PAYPAL,
        ]);
    }

    public function fee(): float
    {
        return match($this) {
            self::CREDIT_CARD => 0.035,      // 3.5%
            self::DEBIT_CARD => 0.025,       // 2.5%
            self::PAYPHONE => 0.02,          // 2%
            self::PAYPAL => 0.045,           // 4.5%
            self::BANK_TRANSFER => 0,        // 0%
            self::CASH_ON_DELIVERY => 0.01,  // 1%
        };
    }
}
```

---

### PaymentStatus

```php
<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::PROCESSING => 'Procesando',
            self::COMPLETED => 'Completado',
            self::FAILED => 'Fallido',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Reembolsado',
            self::PARTIALLY_REFUNDED => 'Reembolso Parcial',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
            self::REFUNDED => 'info',
            self::PARTIALLY_REFUNDED => 'warning',
        };
    }
}
```

---

## 📦 Modelos

### Category

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model implements HasMedia
{
    use HasFactory, HasSlug, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'position',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'position' => 'integer',
    ];

    // ═══════════════════════════════════════════
    // SLUG CONFIGURATION
    // ═══════════════════════════════════════════

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ═══════════════════════════════════════════
    // MEDIA COLLECTIONS
    // ═══════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('position');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banner');
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    public function getAllProductsCountAttribute(): int
    {
        $ids = $this->getAllDescendantIds();
        $ids[] = $this->id;
        return Product::whereIn('category_id', $ids)->count();
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function ancestors(): array
    {
        $ancestors = [];
        $category = $this->parent;

        while ($category) {
            array_unshift($ancestors, $category);
            $category = $category->parent;
        }

        return $ancestors;
    }

    public function getAllDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }

        return $ids;
    }

    public static function buildTree(?int $parentId = null): \Illuminate\Support\Collection
    {
        return static::where('parent_id', $parentId)
            ->active()
            ->ordered()
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->get();
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function isAncestorOf(Category $category): bool
    {
        return in_array($this->id, array_column($category->ancestors(), 'id'));
    }

    public function isDescendantOf(Category $category): bool
    {
        return in_array($category->id, array_column($this->ancestors(), 'id'));
    }
}
```

---

### Brand

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Brand extends Model implements HasMedia
{
    use HasFactory, HasSlug, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'position',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'position' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp']);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo');
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
```

---

### Product

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasFactory, HasSlug, SoftDeletes, InteractsWithMedia, Searchable;

    protected $fillable = [
        'category_id',
        'brand_id',
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'cost',
        'quantity',
        'low_stock_threshold',
        'weight',
        'dimensions',
        'specifications',
        'compatible_models',
        'warranty_months',
        'views_count',
        'sales_count',
        'is_active',
        'is_featured',
        'is_new',
        'requires_shipping',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'specifications' => 'array',
        'compatible_models' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'requires_shipping' => 'boolean',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'warranty_months' => 'integer',
        'views_count' => 'integer',
        'sales_count' => 'integer',
    ];

    // ═══════════════════════════════════════════
    // SLUG & SEARCH CONFIGURATION
    // ═══════════════════════════════════════════

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'category_name' => $this->category?->name,
            'brand_name' => $this->brand?->name,
            'compatible_models' => $this->compatible_models,
            'price' => (float) $this->price,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'quantity' => $this->quantity,
        ];
    }

    // ═══════════════════════════════════════════
    // MEDIA COLLECTIONS
    // ═══════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(400)
            ->height(400)
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(800)
            ->height(800)
            ->sharpen(10);
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id'
        );
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price');
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory($query, $categoryId)
    {
        if (is_array($categoryId)) {
            return $query->whereIn('category_id', $categoryId);
        }
        return $query->where('category_id', $categoryId);
    }

    public function scopeInBrand($query, $brandId)
    {
        if (is_array($brandId)) {
            return $query->whereIn('brand_id', $brandId);
        }
        return $query->where('brand_id', $brandId);
    }

    public function scopePriceBetween($query, $min, $max)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('sales_count');
    }

    public function scopeMostViewed($query)
    {
        return $query->orderByDesc('views_count');
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'medium') ?: null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'thumb') ?: null;
    }

    public function getAllImagesAttribute(): array
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
                'original' => $media->getUrl(),
            ];
        })->toArray();
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getSavingsAmountAttribute(): ?float
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }
        return $this->compare_price - $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->quantity > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->low_stock_threshold;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Agotado',
            'low_stock' => 'Últimas unidades',
            'in_stock' => 'En stock',
        };
    }

    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->reviews()->where('is_approved', true)->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementSales(int $quantity = 1): void
    {
        $this->increment('sales_count', $quantity);
    }

    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }
        $this->decrement('quantity', $quantity);
        return true;
    }

    public function incrementStock(int $quantity = 1): void
    {
        $this->increment('quantity', $quantity);
    }

    public function canPurchase(int $quantity = 1): bool
    {
        return $this->is_active && $this->quantity >= $quantity;
    }

    public function isWishlistedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->wishlists()->where('user_id', $user->id)->exists();
    }

    public function getRelatedProducts(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        // Manual relations first
        $related = $this->relatedProducts()->active()->inStock()->limit($limit)->get();

        // Fill with category products if needed
        if ($related->count() < $limit) {
            $additional = static::query()
                ->where('id', '!=', $this->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->where('category_id', $this->category_id)
                ->active()
                ->inStock()
                ->limit($limit - $related->count())
                ->get();

            $related = $related->merge($additional);
        }

        return $related;
    }

    public function calculatePriceWithTax(): float
    {
        $taxRate = 0.15; // 15% IVA Ecuador
        return $this->price * (1 + $taxRate);
    }
}
```

---

### User

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'cedula',
        'date_of_birth',
        'gender',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'preferences',
        'newsletter_subscribed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
        'newsletter_subscribed' => 'boolean',
    ];

    // ═══════════════════════════════════════════
    // MEDIA COLLECTIONS
    // ═══════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultShippingAddress(): HasOne
    {
        return $this->hasOne(Address::class)
            ->where('type', 'shipping')
            ->where('is_default', true);
    }

    public function defaultBillingAddress(): HasOne
    {
        return $this->hasOne(Address::class)
            ->where('type', 'billing')
            ->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeSubscribers($query)
    {
        return $query->where('newsletter_subscribed', true);
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: $this->default_avatar;
    }

    public function getDefaultAvatarAttribute(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', $this->name)[0];
    }

    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()
            ->where('payment_status', 'completed')
            ->sum('total');
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return '$' . number_format($this->total_spent, 2);
    }

    public function getWishlistCountAttribute(): int
    {
        return $this->wishlists()->count();
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function getOrCreateCart(): Cart
    {
        return $this->cart ?? Cart::create(['user_id' => $this->id]);
    }

    public function addToWishlist(Product $product): Wishlist
    {
        return $this->wishlists()->firstOrCreate([
            'product_id' => $product->id,
        ]);
    }

    public function removeFromWishlist(Product $product): bool
    {
        return $this->wishlists()
            ->where('product_id', $product->id)
            ->delete() > 0;
    }

    public function hasInWishlist(Product $product): bool
    {
        return $this->wishlists()
            ->where('product_id', $product->id)
            ->exists();
    }

    public function setDefaultAddress(Address $address): void
    {
        // Remove default from same type addresses
        $this->addresses()
            ->where('type', $address->type)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }

    public function canPlaceOrder(): bool
    {
        return $this->is_active && $this->email_verified_at !== null;
    }

    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->update(['preferences' => $preferences]);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer') || !$this->hasAnyRole(['admin', 'staff']);
    }
}
```

---

### Order

```php
<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'tax_amount',
        'shipping_amount',
        'total',
        'currency',
        'notes',
        'admin_notes',
        'billing_address',
        'shipping_address',
        'shipping_method',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'CP';
        $date = now()->format('ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeByStatus($query, OrderStatus|array $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->whereIn('status', [
            OrderStatus::CONFIRMED,
            OrderStatus::PROCESSING,
        ]);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', OrderStatus::SHIPPED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', OrderStatus::DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', PaymentStatus::COMPLETED);
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getShippingNameAttribute(): ?string
    {
        return $this->shipping_address['first_name'] ?? null;
    }

    public function getShippingFullNameAttribute(): ?string
    {
        $address = $this->shipping_address;
        if (!$address) return null;
        return trim(($address['first_name'] ?? '') . ' ' . ($address['last_name'] ?? ''));
    }

    public function getShippingFullAddressAttribute(): ?string
    {
        $address = $this->shipping_address;
        if (!$address) return null;

        $parts = array_filter([
            $address['address_line_1'] ?? null,
            $address['address_line_2'] ?? null,
            $address['city'] ?? null,
            $address['province'] ?? null,
            $address['postal_code'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
            OrderStatus::ON_HOLD,
        ]);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::COMPLETED;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === OrderStatus::DELIVERED;
    }

    public function getTrackingUrlAttribute(): ?string
    {
        if (!$this->tracking_number) {
            return null;
        }
        // Servientrega tracking URL
        return "https://www.servientrega.com.ec/rastreo/{$this->tracking_number}";
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function updateStatus(OrderStatus $newStatus, ?string $notes = null, ?User $user = null): bool
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);

        // Record history
        $this->statusHistory()->create([
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'notes' => $notes,
            'user_id' => $user?->id,
        ]);

        // Update timestamps based on status
        if ($newStatus === OrderStatus::SHIPPED && !$this->shipped_at) {
            $this->update(['shipped_at' => now()]);
        } elseif ($newStatus === OrderStatus::DELIVERED && !$this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        } elseif ($newStatus === OrderStatus::CANCELLED && !$this->cancelled_at) {
            $this->update(['cancelled_at' => now()]);
        }

        return true;
    }

    public function cancel(string $reason, ?User $user = null): bool
    {
        if (!$this->can_be_cancelled) {
            return false;
        }

        $this->update(['cancellation_reason' => $reason]);

        return $this->updateStatus(OrderStatus::CANCELLED, $reason, $user);
    }

    public function markAsPaid(): void
    {
        $this->update(['payment_status' => PaymentStatus::COMPLETED]);
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $taxRate = 0.15; // 15% IVA
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal - $this->discount_amount + $taxAmount + $this->shipping_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null, array $options = []): OrderItem
    {
        return $this->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $product->name,
            'sku' => $variant?->sku ?? $product->sku,
            'price' => $variant?->price ?? $product->price,
            'quantity' => $quantity,
            'subtotal' => ($variant?->price ?? $product->price) * $quantity,
            'options' => $options,
        ]);
    }

    public function setShippingAddress(Address $address): void
    {
        $this->update([
            'shipping_address' => $address->toArray(),
        ]);
    }

    public function setBillingAddress(Address $address): void
    {
        $this->update([
            'billing_address' => $address->toArray(),
        ]);
    }
}
```

---

### Cart

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cart) {
            if (empty($cart->session_id)) {
                $cart->session_id = Str::uuid()->toString();
            }
            if (empty($cart->expires_at)) {
                $cart->expires_at = now()->addDays(30);
            }
        });
    }

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->coupon) {
            return 0;
        }

        return $this->coupon->calculateDiscount($this->subtotal, $this->items);
    }

    public function getTaxAmountAttribute(): float
    {
        $taxableAmount = $this->subtotal - $this->discount_amount;
        return $taxableAmount * 0.15; // 15% IVA Ecuador
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal - $this->discount_amount + $this->tax_amount;
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null, array $options = []): CartItem
    {
        // Check if item already exists
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $existingItem->incrementQuantity($quantity);
            return $existingItem->fresh();
        }

        $price = $variant?->price ?? $product->price;

        return $this->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'price' => $price,
            'options' => $options,
        ]);
    }

    public function updateItem(int $itemId, int $quantity): ?CartItem
    {
        $item = $this->items()->find($itemId);

        if (!$item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->update(['quantity' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(int $itemId): bool
    {
        return $this->items()->where('id', $itemId)->delete() > 0;
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update(['coupon_id' => null]);
    }

    public function applyCoupon(Coupon $coupon): bool
    {
        if (!$coupon->isValidFor($this->subtotal, $this->user_id)) {
            return false;
        }

        $this->update(['coupon_id' => $coupon->id]);
        return true;
    }

    public function removeCoupon(): void
    {
        $this->update(['coupon_id' => null]);
    }

    public function syncPrices(): void
    {
        foreach ($this->items as $item) {
            $item->updatePrice();
        }
    }

    public function validateStock(): array
    {
        $issues = [];

        foreach ($this->items as $item) {
            if ($item->hasStockIssue()) {
                $issues[] = [
                    'item_id' => $item->id,
                    'product_name' => $item->product->name,
                    'requested' => $item->quantity,
                    'available' => $item->product->quantity,
                    'message' => $item->getStockMessage(),
                ];
            }
        }

        return $issues;
    }

    public function canCheckout(): bool
    {
        if ($this->is_empty || $this->is_expired) {
            return false;
        }

        return empty($this->validateStock());
    }

    public function mergeWith(Cart $otherCart): void
    {
        foreach ($otherCart->items as $item) {
            $this->addItem(
                $item->product,
                $item->quantity,
                $item->variant,
                $item->options ?? []
            );
        }

        $otherCart->delete();
    }

    public function assignToUser(User $user): void
    {
        // Check if user already has a cart
        $existingCart = Cart::forUser($user->id)->first();

        if ($existingCart && $existingCart->id !== $this->id) {
            $this->mergeWith($existingCart);
        }

        $this->update(['user_id' => $user->id]);
    }

    public function extendExpiration(int $days = 30): void
    {
        $this->update(['expires_at' => now()->addDays($days)]);
    }

    public function toCheckoutData(): array
    {
        return [
            'items' => $this->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'options' => $item->options,
                ];
            })->toArray(),
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'coupon_code' => $this->coupon?->code,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
        ];
    }
}
```

---

### CartItem

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'options' => 'array',
    ];

    // ═══════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getIsAvailableAttribute(): bool
    {
        $stockSource = $this->variant ?? $this->product;
        return $stockSource->is_active && $stockSource->quantity >= $this->quantity;
    }

    public function getMaxQuantityAttribute(): int
    {
        $stockSource = $this->variant ?? $this->product;
        return $stockSource->quantity;
    }

    // ═══════════════════════════════════════════
    // METHODS
    // ═══════════════════════════════════════════

    public function incrementQuantity(int $amount = 1): void
    {
        $this->increment('quantity', $amount);
    }

    public function decrementQuantity(int $amount = 1): void
    {
        $newQuantity = max(1, $this->quantity - $amount);
        $this->update(['quantity' => $newQuantity]);
    }

    public function updatePrice(): void
    {
        $price = $this->variant?->price ?? $this->product->price;
        $this->update(['price' => $price]);
    }

    public function hasStockIssue(): bool
    {
        $stockSource = $this->variant ?? $this->product;
        return $stockSource->quantity < $this->quantity;
    }

    public function getStockMessage(): ?string
    {
        $stockSource = $this->variant ?? $this->product;

        if (!$stockSource->is_active) {
            return 'Este producto ya no está disponible';
        }

        if ($stockSource->quantity <= 0) {
            return 'Producto agotado';
        }

        if ($stockSource->quantity < $this->quantity) {
            return "Solo hay {$stockSource->quantity} unidades disponibles";
        }

        return null;
    }
}
```

---

## 🗃 Migraciones

### 000001_create_categories_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

---

### 000002_create_brands_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
```

---

### 000003_create_products_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->json('specifications')->nullable();
            $table->json('compatible_models')->nullable();
            $table->integer('warranty_months')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(true);
            $table->boolean('requires_shipping')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['is_active', 'is_featured']);
            $table->index(['price']);
            $table->index(['quantity']);
            $table->index(['sales_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

---

### 000004_create_users_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('cedula', 20)->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->json('preferences')->nullable();
            $table->boolean('newsletter_subscribed')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active']);
            $table->index(['cedula']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
```

---

### 000005_create_addresses_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['billing', 'shipping'])->default('shipping');
            $table->string('label', 50)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('cedula', 20)->nullable();
            $table->string('company')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->default('EC');
            $table->string('phone', 20);
            $table->text('delivery_instructions')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
```

---

### 000006_create_coupons_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['fixed', 'percentage', 'free_shipping'])->default('percentage');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_products')->nullable();
            $table->json('excluded_products')->nullable();
            $table->boolean('first_order_only')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
```

---

### 000007_create_orders_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number', 20)->unique();
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('shipping_method', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index(['created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name');
            $table->string('sku', 100);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->text('notes')->nullable();
            $table->timestamp('created_at');

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
```

---

### 000008_create_cart_tables.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['session_id']);
            $table->index(['expires_at']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index(['cart_id', 'product_id']);
            $table->unique(['cart_id', 'product_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
```

---

### 000009_create_additional_tables.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product Variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku', 100)->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });

        // Wishlists
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->unique(['user_id', 'product_id']);
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->string('gateway', 50);
            $table->string('method', 50);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 20)->default('pending');
            $table->json('gateway_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refunded_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['transaction_id']);
        });

        // Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->text('admin_response')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_approved']);
            $table->index(['user_id']);
        });

        // Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('button_text', 50)->nullable();
            $table->string('button_url')->nullable();
            $table->string('position', 50)->default('home_slider');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['position', 'is_active', 'sort_order']);
        });

        // Newsletter Subscribers
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('source', 50)->default('footer');
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
        });

        // Related Products (pivot)
        Schema::create('related_products', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['product_id', 'related_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_products');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('product_variants');
    }
};
```

---

## 🎨 Filament Admin

### ProductResource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::lowStock()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Información General')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => 
                                    $set('slug', \Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),

                            Forms\Components\TextInput::make('sku')
                                ->label('SKU')
                                ->required()
                                ->maxLength(100)
                                ->unique(ignoreRecord: true),

                            Forms\Components\RichEditor::make('description')
                                ->label('Descripción')
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('short_description')
                                ->label('Descripción Corta')
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make('Imágenes')
                        ->schema([
                            Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                                ->label('Imágenes del Producto')
                                ->collection('images')
                                ->multiple()
                                ->maxFiles(10)
                                ->reorderable()
                                ->image()
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1:1'),
                        ]),

                    Forms\Components\Section::make('Precios')
                        ->schema([
                            Forms\Components\TextInput::make('price')
                                ->label('Precio')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0),

                            Forms\Components\TextInput::make('compare_price')
                                ->label('Precio Comparación')
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0)
                                ->helperText('Precio anterior o PVP'),

                            Forms\Components\TextInput::make('cost')
                                ->label('Costo')
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0),
                        ])->columns(3),

                    Forms\Components\Section::make('Inventario')
                        ->schema([
                            Forms\Components\TextInput::make('quantity')
                                ->label('Cantidad en Stock')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),

                            Forms\Components\TextInput::make('low_stock_threshold')
                                ->label('Alerta Stock Bajo')
                                ->numeric()
                                ->minValue(0)
                                ->default(5),
                        ])->columns(2),

                    Forms\Components\Section::make('Envío')
                        ->schema([
                            Forms\Components\Toggle::make('requires_shipping')
                                ->label('Requiere Envío')
                                ->default(true),

                            Forms\Components\TextInput::make('weight')
                                ->label('Peso (kg)')
                                ->numeric()
                                ->suffix('kg'),

                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('dimensions.length')
                                        ->label('Largo (cm)')
                                        ->numeric(),
                                    Forms\Components\TextInput::make('dimensions.width')
                                        ->label('Ancho (cm)')
                                        ->numeric(),
                                    Forms\Components\TextInput::make('dimensions.height')
                                        ->label('Alto (cm)')
                                        ->numeric(),
                                ]),
                        ]),

                    Forms\Components\Section::make('Especificaciones')
                        ->schema([
                            Forms\Components\KeyValue::make('specifications')
                                ->label('Especificaciones Técnicas')
                                ->keyLabel('Característica')
                                ->valueLabel('Valor')
                                ->addActionLabel('Agregar especificación'),

                            Forms\Components\TagsInput::make('compatible_models')
                                ->label('Modelos Compatibles')
                                ->placeholder('Agregar modelo'),
                        ]),

                    Forms\Components\Section::make('SEO')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('Meta Título')
                                ->maxLength(255),

                            Forms\Components\Textarea::make('meta_description')
                                ->label('Meta Descripción')
                                ->maxLength(160),
                        ])->collapsed(),
                ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Estado')
                        ->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('Activo')
                                ->default(true),

                            Forms\Components\Toggle::make('is_featured')
                                ->label('Destacado')
                                ->default(false),

                            Forms\Components\Toggle::make('is_new')
                                ->label('Nuevo')
                                ->default(true),
                        ]),

                    Forms\Components\Section::make('Organización')
                        ->schema([
                            Forms\Components\Select::make('category_id')
                                ->label('Categoría')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Select::make('brand_id')
                                ->label('Marca')
                                ->relationship('brand', 'name')
                                ->searchable()
                                ->preload(),
                        ]),

                    Forms\Components\Section::make('Garantía')
                        ->schema([
                            Forms\Components\TextInput::make('warranty_months')
                                ->label('Garantía (meses)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->suffix('meses'),
                        ]),

                    Forms\Components\Section::make('Estadísticas')
                        ->schema([
                            Forms\Components\Placeholder::make('views_count')
                                ->label('Vistas')
                                ->content(fn ($record) => $record?->views_count ?? 0),

                            Forms\Components\Placeholder::make('sales_count')
                                ->label('Ventas')
                                ->content(fn ($record) => $record?->sales_count ?? 0),

                            Forms\Components\Placeholder::make('created_at')
                                ->label('Creado')
                                ->content(fn ($record) => $record?->created_at?->diffForHumans()),

                            Forms\Components\Placeholder::make('updated_at')
                                ->label('Actualizado')
                                ->content(fn ($record) => $record?->updated_at?->diffForHumans()),
                        ])->hiddenOn('create'),
                ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->quantity <= 0 => 'danger',
                        $record->quantity <= $record->low_stock_threshold => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Marca')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacado'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query) => $query->lowStock()),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Sin Stock')
                    ->query(fn (Builder $query) => $query->outOfStock()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->excludeAttributes(['slug', 'sku', 'views_count', 'sales_count']),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
```

---

### CategoryResource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Información Básica')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => 
                                    $set('slug', \Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),

                            Forms\Components\Select::make('parent_id')
                                ->label('Categoría Padre')
                                ->relationship('parent', 'name', fn (Builder $query) => 
                                    $query->whereNull('parent_id'))
                                ->searchable()
                                ->preload()
                                ->placeholder('Ninguna (categoría raíz)'),

                            Forms\Components\Textarea::make('description')
                                ->label('Descripción')
                                ->rows(3),

                            Forms\Components\TextInput::make('icon')
                                ->label('Icono')
                                ->maxLength(100)
                                ->placeholder('heroicon-o-computer-desktop'),

                            Forms\Components\TextInput::make('position')
                                ->label('Posición')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ])->columns(2),

                    Forms\Components\Section::make('Imágenes')
                        ->schema([
                            Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                                ->label('Imagen')
                                ->collection('image')
                                ->image(),

                            Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                                ->label('Banner')
                                ->collection('banner')
                                ->image(),
                        ])->columns(2),

                    Forms\Components\Section::make('SEO')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('Meta Título')
                                ->maxLength(255),

                            Forms\Components\Textarea::make('meta_description')
                                ->label('Meta Descripción')
                                ->maxLength(160),
                        ])->collapsed(),
                ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Estado')
                        ->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('Activo')
                                ->default(true),

                            Forms\Components\Toggle::make('is_featured')
                                ->label('Destacada')
                                ->default(false),
                        ]),

                    Forms\Components\Section::make('Información')
                        ->schema([
                            Forms\Components\Placeholder::make('products_count')
                                ->label('Productos')
                                ->content(fn ($record) => $record?->products_count ?? 0),

                            Forms\Components\Placeholder::make('created_at')
                                ->label('Creado')
                                ->content(fn ($record) => $record?->created_at?->diffForHumans()),
                        ])->hiddenOn('create'),
                ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->label('')
                    ->collection('image')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoría Padre')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Posición')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacada')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Categoría Padre')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacada'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
```

---

### OrderResource

```php
<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Información del Pedido')
                        ->schema([
                            Forms\Components\TextInput::make('order_number')
                                ->label('Número de Pedido')
                                ->disabled(),

                            Forms\Components\Placeholder::make('created_at')
                                ->label('Fecha')
                                ->content(fn ($record) => $record->created_at->format('d/m/Y H:i')),

                            Forms\Components\Placeholder::make('user.name')
                                ->label('Cliente')
                                ->content(fn ($record) => $record->user?->name ?? 'Invitado'),

                            Forms\Components\Placeholder::make('user.email')
                                ->label('Email')
                                ->content(fn ($record) => $record->user?->email ?? '-'),
                        ])->columns(2),

                    Forms\Components\Section::make('Estado')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Estado del Pedido')
                                ->options(collect(OrderStatus::cases())->mapWithKeys(
                                    fn ($status) => [$status->value => $status->label()]
                                ))
                                ->required(),

                            Forms\Components\Select::make('payment_status')
                                ->label('Estado del Pago')
                                ->options(collect(PaymentStatus::cases())->mapWithKeys(
                                    fn ($status) => [$status->value => $status->label()]
                                ))
                                ->required(),

                            Forms\Components\Select::make('payment_method')
                                ->label('Método de Pago')
                                ->options(collect(PaymentMethod::cases())->mapWithKeys(
                                    fn ($method) => [$method->value => $method->label()]
                                )),
                        ])->columns(3),

                    Forms\Components\Section::make('Envío')
                        ->schema([
                            Forms\Components\TextInput::make('tracking_number')
                                ->label('Número de Guía'),

                            Forms\Components\TextInput::make('shipping_method')
                                ->label('Método de Envío'),

                            Forms\Components\DateTimePicker::make('shipped_at')
                                ->label('Fecha de Envío'),

                            Forms\Components\DateTimePicker::make('delivered_at')
                                ->label('Fecha de Entrega'),
                        ])->columns(2),

                    Forms\Components\Section::make('Notas')
                        ->schema([
                            Forms\Components\Textarea::make('notes')
                                ->label('Notas del Cliente')
                                ->disabled()
                                ->rows(2),

                            Forms\Components\Textarea::make('admin_notes')
                                ->label('Notas Internas')
                                ->rows(2),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Resumen')
                        ->schema([
                            Forms\Components\Placeholder::make('subtotal')
                                ->label('Subtotal')
                                ->content(fn ($record) => '$' . number_format($record->subtotal, 2)),

                            Forms\Components\Placeholder::make('discount_amount')
                                ->label('Descuento')
                                ->content(fn ($record) => '-$' . number_format($record->discount_amount, 2))
                                ->visible(fn ($record) => $record->discount_amount > 0),

                            Forms\Components\Placeholder::make('tax_amount')
                                ->label('IVA (15%)')
                                ->content(fn ($record) => '$' . number_format($record->tax_amount, 2)),

                            Forms\Components\Placeholder::make('shipping_amount')
                                ->label('Envío')
                                ->content(fn ($record) => '$' . number_format($record->shipping_amount, 2)),

                            Forms\Components\Placeholder::make('total')
                                ->label('Total')
                                ->content(fn ($record) => '$' . number_format($record->total, 2)),
                        ]),

                    Forms\Components\Section::make('Dirección de Envío')
                        ->schema([
                            Forms\Components\Placeholder::make('shipping_address')
                                ->label('')
                                ->content(fn ($record) => view('filament.components.address', [
                                    'address' => $record->shipping_address
                                ])),
                        ]),
                ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('# Pedido')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->default('Invitado'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => $state->color()),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state) => $state->label())
                    ->color(fn (PaymentStatus $state) => $state->color()),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(fn (?PaymentMethod $state) => $state?->label() ?? '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Guía')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->multiple()
                    ->options(collect(OrderStatus::cases())->mapWithKeys(
                        fn ($status) => [$status->value => $status->label()]
                    )),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Estado Pago')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(
                        fn ($status) => [$status->value => $status->label()]
                    )),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método Pago')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(
                        fn ($method) => [$method->value => $method->label()]
                    )),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === OrderStatus::PENDING)
                    ->action(fn ($record) => $record->updateStatus(OrderStatus::CONFIRMED)),

                Tables\Actions\Action::make('ship')
                    ->label('Enviar')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Número de Guía')
                            ->required(),
                    ])
                    ->visible(fn ($record) => in_array($record->status, [OrderStatus::CONFIRMED, OrderStatus::PROCESSING]))
                    ->action(function ($record, array $data) {
                        $record->update(['tracking_number' => $data['tracking_number']]);
                        $record->updateStatus(OrderStatus::SHIPPED);
                    }),

                Tables\Actions\Action::make('deliver')
                    ->label('Entregado')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === OrderStatus::SHIPPED)
                    ->action(fn ($record) => $record->updateStatus(OrderStatus::DELIVERED)),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo de Cancelación')
                            ->required(),
                    ])
                    ->visible(fn ($record) => $record->can_be_cancelled)
                    ->action(fn ($record, array $data) => $record->cancel($data['reason'])),

                Tables\Actions\Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('admin.orders.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
```

---

## 🌐 API REST

### Base URL
```
https://api.compupiezas.net/api/v1
```

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | /auth/register | Registro de usuario |
| POST | /auth/login | Iniciar sesión |
| POST | /auth/logout | Cerrar sesión |
| POST | /auth/forgot-password | Solicitar reset de password |
| POST | /auth/reset-password | Resetear password |
| GET | /auth/user | Obtener usuario autenticado |

### Catálogo

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | /categories | Listar categorías |
| GET | /categories/{slug} | Detalle de categoría |
| GET | /brands | Listar marcas |
| GET | /products | Listar productos (paginado, filtros) |
| GET | /products/{slug} | Detalle de producto |
| GET | /products/featured | Productos destacados |
| GET | /products/on-sale | Productos en oferta |
| GET | /products/new-arrivals | Nuevos productos |
| GET | /search | Búsqueda de productos |

**Filtros de productos:**
- `category`: Slug de categoría
- `brand`: Slug de marca
- `min_price`, `max_price`: Rango de precios
- `in_stock`: Solo en stock
- `sort`: price_asc, price_desc, newest, popular

### Carrito

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | /cart | Obtener carrito actual |
| POST | /cart/items | Agregar item al carrito |
| PATCH | /cart/items/{id} | Actualizar cantidad |
| DELETE | /cart/items/{id} | Eliminar item |
| POST | /cart/coupon | Aplicar cupón |
| DELETE | /cart/coupon | Remover cupón |

### Checkout

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | /checkout | Procesar orden |
| GET | /checkout/shipping-methods | Métodos de envío disponibles |
| POST | /checkout/validate-address | Validar dirección |

### Pedidos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | /orders | Listar pedidos del usuario |
| GET | /orders/{number} | Detalle de pedido |
| POST | /orders/{number}/cancel | Cancelar pedido |

### Wishlist

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | /wishlist | Obtener wishlist |
| POST | /wishlist/{productId} | Agregar a wishlist |
| DELETE | /wishlist/{productId} | Eliminar de wishlist |

### Usuario

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | /user/profile | Obtener perfil |
| PATCH | /user/profile | Actualizar perfil |
| GET | /user/addresses | Listar direcciones |
| POST | /user/addresses | Crear dirección |
| PATCH | /user/addresses/{id} | Actualizar dirección |
| DELETE | /user/addresses/{id} | Eliminar dirección |

---

## 🔌 Integraciones

### Payment Gateways

#### PayPhone
```php
interface PaymentGatewayInterface
{
    public function createPayment(Order $order): PaymentResponse;
    public function verifyPayment(string $transactionId): PaymentStatus;
    public function refund(Order $order, float $amount): RefundResponse;
    public function getWebhookHandler(): WebhookHandler;
}
```

**Gateways soportados:**
- PayPhone (billetera móvil)
- Datafast/Alignet (tarjetas crédito/débito)
- PayPal (internacional)
- Transferencia Bancaria (Pichincha, Guayaquil)
- Pago Contra Entrega

### Shipping - Servientrega

```php
interface ShippingProviderInterface
{
    public function calculateRate(Address $from, Address $to, array $items): ShippingRate;
    public function createShipment(Order $order): Shipment;
    public function getTrackingInfo(string $trackingNumber): TrackingInfo;
    public function getLabel(string $shipmentId): string;
    public function cancelShipment(string $shipmentId): bool;
}
```

**URL de Tracking:**
```
https://www.servientrega.com.ec/rastreo/{tracking_number}
```

---

## 🐳 Configuración Docker

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
      - redis
      - meilisearch

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: compupiezas
      MYSQL_USER: compupiezas
      MYSQL_PASSWORD: secret
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

  meilisearch:
    image: getmeili/meilisearch:latest
    environment:
      MEILI_MASTER_KEY: masterkey
    ports:
      - "7700:7700"
    volumes:
      - meilisearch_data:/meili_data

  horizon:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan horizon
    depends_on:
      - app
      - redis

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan schedule:work
    depends_on:
      - app

volumes:
  mysql_data:
  redis_data:
  meilisearch_data:
```

---

## 🚀 Instalación

### Requisitos
- PHP 8.2+
- Composer 2.x
- Node.js 18+ / npm
- MySQL 8.0 / PostgreSQL 16
- Redis
- Meilisearch

### Pasos

```bash
# 1. Clonar repositorio
git clone https://github.com/compupiezas/ecommerce.git
cd ecommerce

# 2. Instalar dependencias PHP
composer install

# 3. Copiar configuración
cp .env.example .env

# 4. Generar key
php artisan key:generate

# 5. Configurar .env
# - Base de datos
# - Redis
# - Meilisearch
# - Mail

# 6. Ejecutar migraciones
php artisan migrate

# 7. Crear usuario admin
php artisan make:filament-user

# 8. Instalar dependencias frontend
npm install
npm run build

# 9. Enlazar storage
php artisan storage:link

# 10. Indexar productos para búsqueda
php artisan scout:import "App\Models\Product"

# 11. Iniciar servidor
php artisan serve

# 12. Iniciar Horizon (colas)
php artisan horizon
```

### Seeders (opcional)

```bash
# Datos de prueba
php artisan db:seed

# Datos específicos
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=BrandSeeder
php artisan db:seed --class=ProductSeeder
```

### URLs

| Recurso | URL |
|---------|-----|
| Sitio web | http://localhost |
| Panel Admin | http://localhost/admin |
| API | http://localhost/api/v1 |
| Horizon | http://localhost/horizon |
| Meilisearch | http://localhost:7700 |

---

## 📅 Roadmap

### Fase 1: Core (2-3 semanas)
- [ ] Completar setup Laravel + Filament
- [ ] Ejecutar migraciones
- [ ] Crear seeders con datos de ejemplo
- [ ] Completar recursos admin
- [ ] Testing CRUD

### Fase 2: Frontend (2-3 semanas)
- [ ] Diseño layout principal
- [ ] Homepage con banners
- [ ] Catálogo con filtros
- [ ] Página de producto
- [ ] Carrito de compras

### Fase 3: Checkout (1-2 semanas)
- [ ] Flujo de checkout
- [ ] Integración pasarelas de pago
- [ ] Integración Servientrega
- [ ] Emails transaccionales

### Fase 4: Usuarios (1 semana)
- [ ] Registro/login
- [ ] Perfil de usuario
- [ ] Historial de pedidos
- [ ] Wishlist

### Fase 5: Optimización (1 semana)
- [ ] SEO
- [ ] Performance
- [ ] Testing
- [ ] Documentación

---

## 📝 Licencia

Propietario - CompuPiezas Ecuador

---

*Documento generado: Enero 2026*
*Versión: 1.0.0*
