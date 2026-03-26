<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GeneralSetting extends Model
{
    use BelongsToTenant, LogsActivity;

    protected static function booted(): void
    {
        static::saved(fn (self $setting) => cache()->forget('general_settings_tenant_' . ($setting->tenant_id ?? 'global')));
        static::deleted(fn (self $setting) => cache()->forget('general_settings_tenant_' . ($setting->tenant_id ?? 'global')));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['site_name', 'tax_rate', 'sri_environment', 'sri_ruc', 'default_language', 'theme_color'])
            ->logOnlyDirty()
            ->useLogName('settings')
            ->setDescriptionForEvent(fn (string $eventName): string => "Configuracion general {$eventName}");
    }

    protected $fillable = [
        'site_name',
        'site_logo',
        'site_favicon',
        'theme_color',
        'default_language',
        'tax_rate',
        'sri_ruc',
        'sri_company_name',
        'sri_commercial_name',
        'sri_establishment_address',
        'sri_signature_path',
        'sri_signature_password',
        'sri_environment',
        'sri_establishment_code',
        'sri_emission_point_code',
        'sri_contribution_type',
        'sri_accounting_required',
        'sri_signature_valid_from',
        'sri_signature_expires_at',
        'sri_next_sequence',
        'mail_from_name',
        'mail_from_address',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'navbar_config',
        'footer_config',
        'typography_config',
        'custom_css',
        'social_links',
        'product_page_config',
        'shop_page_config',
        'about_page_config',
        'brands_page_config',
        'store_policies',
        'domain_config',
        'seo_config',
        'payment_gateways_config',
        'shipping_config',
        'currency_code',
        'currency_symbol',
        'quotation_validity_days',
        'quotation_prefix',
        'cart_expiration_days',
        'abandoned_cart_reminder_hours',
        'low_stock_threshold',
        'notification_config',
        'social_login_config',
        'require_account_approval',
    ];

    protected $casts = [
        'site_logo' => 'string',
        'site_favicon' => 'string',
        'tax_rate' => 'float',
        'quotation_validity_days' => 'integer',
        'cart_expiration_days' => 'integer',
        'abandoned_cart_reminder_hours' => 'integer',
        'low_stock_threshold' => 'integer',
        'sri_accounting_required' => 'boolean',
        'sri_environment' => 'integer',
        'sri_signature_valid_from' => 'datetime',
        'sri_signature_expires_at' => 'datetime',
        'sri_next_sequence' => 'integer',
        'smtp_port' => 'integer',
        'smtp_password' => 'encrypted',
        'sri_signature_password' => 'encrypted',
        'navbar_config' => 'array',
        'footer_config' => 'array',
        'typography_config' => 'array',
        'social_links' => 'array',
        'product_page_config' => 'array',
        'shop_page_config' => 'array',
        'about_page_config' => 'array',
        'brands_page_config' => 'array',
        'store_policies' => 'array',
        'domain_config' => 'array',
        'seo_config' => 'array',
        'payment_gateways_config' => 'array',
        'shipping_config' => 'array',
        'notification_config' => 'array',
        'social_login_config' => 'array',
        'require_account_approval' => 'boolean',
    ];

    /**
     * Get the current tenant's settings, cached for the duration of the request.
     * Falls back to a 60-second cache to avoid repeated DB hits across requests.
     */
    public static function cached(): ?static
    {
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')?->id : null;
        $cacheKey = 'general_settings_tenant_' . ($tenantId ?? 'global');

        return cache()->remember($cacheKey, 60, function () use ($tenantId) {
            if ($tenantId) {
                return static::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->first();
            }

            return static::first();
        });
    }

    /**
     * Flush the cached settings for the current tenant.
     */
    public static function flushCache(): void
    {
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')?->id : null;
        cache()->forget('general_settings_tenant_' . ($tenantId ?? 'global'));
    }

    /**
     * Get the settings for the current tenant, bypassing the global scope
     * to ensure explicit tenant filtering (safe even for SuperAdmin).
     */
    public static function forCurrentTenant(): ?static
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return null;
        }

        return static::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->first();
    }

    /**
     * Get or create the settings for the current tenant.
     * Uses explicit tenant filtering to prevent cross-tenant data leakage.
     */
    public static function forCurrentTenantOrCreate(array $defaults = []): static
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            throw new \RuntimeException('No tenant context available.');
        }

        return static::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            array_merge([
                'site_name' => config('app.name'),
                'theme_color' => 'indigo',
                'default_language' => 'es',
            ], $defaults)
        );
    }

    public function getNotificationConfig(): array
    {
        return array_merge([
            'order_confirmed' => true,
            'order_shipped' => true,
            'order_delivered' => true,
            'order_cancelled' => true,
            'payment_received' => true,
            'refund_processed' => true,
            'welcome_email' => true,
            'abandoned_cart' => false,
            'review_request' => false,
            'low_stock_admin' => true,
            'new_order_admin' => true,
            'new_return_admin' => true,
            'new_ticket_admin' => true,
        ], $this->notification_config ?? []);
    }

    public function getSocialLoginConfig(): array
    {
        return array_merge([
            'google' => ['enabled' => false, 'client_id' => '', 'client_secret' => ''],
            'facebook' => ['enabled' => false, 'client_id' => '', 'client_secret' => ''],
            'apple' => ['enabled' => false, 'client_id' => '', 'client_secret' => ''],
        ], $this->social_login_config ?? []);
    }

    public function hasSmtpConfigured(): bool
    {
        return $this->smtp_host && $this->smtp_port && $this->mail_from_address;
    }

    public function getNavbarConfig(): array
    {
        return array_merge([
            'menu_items' => [],
            'show_search' => true,
            'show_categories_button' => true,
            'style' => 'transparent_on_scroll',
        ], $this->navbar_config ?? []);
    }

    public function getFooterConfig(): array
    {
        return array_merge([
            'brand_description' => '',
            'columns' => [],
            'show_newsletter' => true,
            'copyright_text' => '',
            'show_payment_icons' => false,
        ], $this->footer_config ?? []);
    }

    public function getTypographyConfig(): array
    {
        return array_merge([
            'body_font' => '',
            'heading_font' => '',
            'font_size_scale' => 'normal',
        ], $this->typography_config ?? []);
    }

    public function getSocialLinks(): array
    {
        return array_merge([
            'facebook_url' => '',
            'instagram_url' => '',
            'tiktok_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
            'whatsapp_number' => '',
        ], $this->social_links ?? []);
    }

    public function getProductPageConfig(): array
    {
        return array_merge([
            'show_related_products' => true,
            'show_reviews' => true,
            'show_specifications' => true,
            'related_products_count' => 4,
        ], $this->product_page_config ?? []);
    }

    public function getShopPageConfig(): array
    {
        return array_merge([
            'products_per_page' => 12,
            'grid_columns' => 3,
            'show_brand_filter' => true,
            'show_category_filter' => true,
        ], $this->shop_page_config ?? []);
    }

    public function getAboutPageConfig(): array
    {
        return array_merge([
            'title' => '',
            'description' => '',
            'values' => [],
            'team_image' => '',
            'cta_text' => '',
        ], $this->about_page_config ?? []);
    }

    public function getBrandsPageConfig(): array
    {
        return array_merge([
            'is_enabled' => true,
            'title' => '',
            'subtitle' => '',
            'meta_title' => '',
            'meta_description' => '',
            'columns' => 6,
            'show_product_count' => false,
        ], $this->brands_page_config ?? []);
    }

    public function getStorePolicies(): array
    {
        return $this->store_policies ?? [];
    }

    public function getStorePolicyBySlug(string $slug): ?array
    {
        foreach ($this->getStorePolicies() as $policy) {
            if (($policy['slug'] ?? '') === $slug) {
                return $policy;
            }
        }

        return null;
    }

    public function getDomainConfig(): array
    {
        return array_merge([
            'custom_domain' => '',
            'dns_verified' => false,
        ], $this->domain_config ?? []);
    }

    public function getPaymentGatewaysConfig(): array
    {
        return array_merge([
            'quotations_enabled' => true,
            'quotation_only_mode' => false,
            'nuvei_enabled' => false,
            'nuvei_merchant_id' => '',
            'nuvei_site_id' => '',
            'nuvei_secret_key' => '',
            'nuvei_environment' => 'test',
            'nuvei_surcharge_percentage' => 0,
            'payphone_enabled' => false,
            'payphone_token' => '',
            'payphone_store_id' => '',
            'payphone_environment' => 'test',
            'payphone_surcharge_percentage' => 0,
            'kushki_enabled' => false,
            'kushki_public_key' => '',
            'kushki_private_key' => '',
            'kushki_environment' => 'test',
            'kushki_surcharge_percentage' => 0,
            'bank_transfer_enabled' => false,
            'bank_transfer_instructions' => '',
            'bank_transfer_surcharge_percentage' => 0,
            'bank_transfer_requires_proof' => true,
            'cash_on_delivery_enabled' => false,
            'cash_on_delivery_instructions' => '',
            'cash_on_delivery_surcharge_percentage' => 0,
            'cash_on_delivery_requires_proof' => false,
        ], $this->payment_gateways_config ?? []);
    }

    public function isQuotationsEnabled(): bool
    {
        $config = $this->getPaymentGatewaysConfig();

        return (bool) ($config['quotations_enabled'] ?? true);
    }

    public function isQuotationOnlyMode(): bool
    {
        $config = $this->getPaymentGatewaysConfig();

        return (bool) ($config['quotation_only_mode'] ?? false) && $this->isQuotationsEnabled();
    }

    /**
     * Get available payment methods derived from gateway config.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function getAvailablePaymentMethods(): \Illuminate\Support\Collection
    {
        $config = $this->getPaymentGatewaysConfig();
        $methods = collect();

        if ($config['nuvei_enabled'] ?? false) {
            $methods->push((object) [
                'key' => \App\Enums\PaymentMethod::CREDIT_CARD->value,
                'enum' => \App\Enums\PaymentMethod::CREDIT_CARD,
                'name' => 'Tarjeta de Credito / Debito',
                'slug' => 'nuvei',
                'surcharge_percentage' => (float) ($config['nuvei_surcharge_percentage'] ?? 0),
                'requires_proof' => false,
                'instructions' => null,
                'icon' => 'heroicon-o-credit-card',
                'gateway' => 'nuvei',
            ]);
        }

        if ($config['payphone_enabled'] ?? false) {
            $methods->push((object) [
                'key' => \App\Enums\PaymentMethod::PAYPHONE->value,
                'enum' => \App\Enums\PaymentMethod::PAYPHONE,
                'name' => 'PayPhone',
                'slug' => 'payphone',
                'surcharge_percentage' => (float) ($config['payphone_surcharge_percentage'] ?? 0),
                'requires_proof' => false,
                'instructions' => null,
                'icon' => 'heroicon-o-device-phone-mobile',
                'gateway' => 'payphone',
            ]);
        }

        if ($config['kushki_enabled'] ?? false) {
            $methods->push((object) [
                'key' => \App\Enums\PaymentMethod::KUSHKI->value,
                'enum' => \App\Enums\PaymentMethod::KUSHKI,
                'name' => 'Kushki / Datafast',
                'slug' => 'kushki',
                'surcharge_percentage' => (float) ($config['kushki_surcharge_percentage'] ?? 0),
                'requires_proof' => false,
                'instructions' => null,
                'icon' => 'heroicon-o-credit-card',
                'gateway' => 'kushki',
            ]);
        }

        if ($config['bank_transfer_enabled'] ?? false) {
            $methods->push((object) [
                'key' => \App\Enums\PaymentMethod::BANK_TRANSFER->value,
                'enum' => \App\Enums\PaymentMethod::BANK_TRANSFER,
                'name' => 'Transferencia Bancaria',
                'slug' => 'transferencia-bancaria',
                'surcharge_percentage' => (float) ($config['bank_transfer_surcharge_percentage'] ?? 0),
                'requires_proof' => (bool) ($config['bank_transfer_requires_proof'] ?? true),
                'instructions' => $config['bank_transfer_instructions'] ?? null,
                'icon' => 'heroicon-o-building-library',
                'gateway' => 'bank_transfer',
            ]);
        }

        if ($config['cash_on_delivery_enabled'] ?? false) {
            $methods->push((object) [
                'key' => \App\Enums\PaymentMethod::CASH_ON_DELIVERY->value,
                'enum' => \App\Enums\PaymentMethod::CASH_ON_DELIVERY,
                'name' => 'Pago Contra Entrega',
                'slug' => 'contra-entrega',
                'surcharge_percentage' => (float) ($config['cash_on_delivery_surcharge_percentage'] ?? 0),
                'requires_proof' => (bool) ($config['cash_on_delivery_requires_proof'] ?? false),
                'instructions' => $config['cash_on_delivery_instructions'] ?? null,
                'icon' => 'heroicon-o-banknotes',
                'gateway' => 'cash_on_delivery',
            ]);
        }

        return $methods;
    }

    public function getGatewayConfig(string $gateway): array
    {
        $config = $this->getPaymentGatewaysConfig();
        $prefix = $gateway . '_';

        return collect($config)
            ->filter(fn ($value, $key) => str_starts_with($key, $prefix))
            ->mapWithKeys(fn ($value, $key) => [str_replace($prefix, '', $key) => $value])
            ->toArray();
    }

    public function isGatewayEnabled(string $gateway): bool
    {
        return (bool) ($this->getPaymentGatewaysConfig()[$gateway . '_enabled'] ?? false);
    }

    /**
     * Get tenant tax rate as a decimal (e.g. 0.15 for 15%).
     * Uses cache to avoid repeated DB queries.
     */
    public static function getTaxRateDecimal(): float
    {
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : 'global';

        $rate = cache()->remember(
            "tenant_{$tenantId}_tax_rate",
            3600,
            fn () => static::withoutGlobalScopes()->where('tenant_id', app()->bound('current_tenant') ? app('current_tenant')->id : null)->first()?->tax_rate ?? 15.00
        );

        return $rate / 100;
    }

    /**
     * Get tenant tax rate as a percentage (e.g. 15.00).
     */
    public static function getTaxRatePercentage(): float
    {
        return static::getTaxRateDecimal() * 100;
    }

    /**
     * Get SRI tax code for the current rate.
     * Ecuador SRI codes: 0=0%, 2=12%, 3=14%, 4=15%, 5=5%
     */
    public static function getSriTaxCode(): string
    {
        $rate = static::getTaxRatePercentage();

        return match (true) {
            $rate == 0 => '0',
            $rate == 5 => '5',
            $rate == 12 => '2',
            $rate == 14 => '3',
            $rate >= 15 => '4',
            default => '4',
        };
    }

    /**
     * Get cached tenant setting value with fallback.
     */
    public static function getCachedValue(string $field, mixed $default = null): mixed
    {
        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : 'global';

        return cache()->remember(
            "tenant_{$tenantId}_{$field}",
            3600,
            fn () => static::withoutGlobalScopes()
                ->where('tenant_id', app()->bound('current_tenant') ? app('current_tenant')->id : null)
                ->first()?->{$field} ?? $default
        );
    }

    public static function getCurrencySymbol(): string
    {
        return static::getCachedValue('currency_symbol', '$');
    }

    public static function getCurrencyCode(): string
    {
        return static::getCachedValue('currency_code', 'USD');
    }

    public static function getQuotationValidityDays(): int
    {
        return (int) static::getCachedValue('quotation_validity_days', 15);
    }

    public static function getQuotationPrefix(): string
    {
        return static::getCachedValue('quotation_prefix', 'COT');
    }

    public static function getCartExpirationDays(): int
    {
        return (int) static::getCachedValue('cart_expiration_days', 30);
    }

    public static function getAbandonedCartReminderHours(): int
    {
        return (int) static::getCachedValue('abandoned_cart_reminder_hours', 24);
    }

    public static function getLowStockThreshold(): int
    {
        return (int) static::getCachedValue('low_stock_threshold', 5);
    }

    public function getShippingConfig(): array
    {
        return array_merge([
            'carriers' => [],
        ], $this->shipping_config ?? []);
    }

    /**
     * Get available shipping rates derived from shipping config.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function getAvailableShippingRates(?float $orderAmount = null, ?float $weight = null, ?string $zone = null): \Illuminate\Support\Collection
    {
        $config = $this->getShippingConfig();
        $rates = collect();

        foreach ($config['carriers'] ?? [] as $carrier) {
            if (! ($carrier['is_active'] ?? true)) {
                continue;
            }

            foreach ($carrier['rates'] ?? [] as $index => $rate) {
                if (! ($rate['is_active'] ?? true)) {
                    continue;
                }

                // Filter by order amount
                if ($orderAmount !== null) {
                    $min = (float) ($rate['min_order_amount'] ?? 0);
                    $max = $rate['max_order_amount'] ?? null;
                    if ($min > 0 && $orderAmount < $min) {
                        continue;
                    }
                    if ($max !== null && (float) $max > 0 && $orderAmount > (float) $max) {
                        continue;
                    }
                }

                // Filter by weight
                if ($weight !== null) {
                    $minW = $rate['min_weight'] ?? null;
                    $maxW = $rate['max_weight'] ?? null;
                    if ($minW !== null && (float) $minW > 0 && $weight < (float) $minW) {
                        continue;
                    }
                    if ($maxW !== null && (float) $maxW > 0 && $weight > (float) $maxW) {
                        continue;
                    }
                }

                // Filter by zone
                if ($zone !== null && ! empty($rate['zone']) && $rate['zone'] !== $zone) {
                    continue;
                }

                $key = \Illuminate\Support\Str::slug(($carrier['key'] ?? $carrier['name'] ?? '') . '-' . ($rate['name'] ?? $index));

                $rates->push((object) [
                    'key' => $key,
                    'carrier_key' => $carrier['key'] ?? \Illuminate\Support\Str::slug($carrier['name'] ?? ''),
                    'carrier_name' => $carrier['name'] ?? '',
                    'carrier_logo' => $carrier['logo_url'] ?? null,
                    'rate_name' => $rate['name'] ?? '',
                    'price' => (float) ($rate['price'] ?? 0),
                    'estimated_days' => $rate['estimated_days'] ?? null,
                    'zone' => $rate['zone'] ?? null,
                    'tracking_url_template' => $carrier['tracking_url_template'] ?? null,
                ]);
            }
        }

        return $rates->sortBy('price')->values();
    }

    public static function requiresAccountApproval(): bool
    {
        return (bool) static::getCachedValue('require_account_approval', false);
    }

    public function getSeoConfig(): array
    {
        return array_merge([
            'home_meta_title' => '',
            'home_meta_description' => '',
            'home_meta_keywords' => '',
            'org_name' => '',
            'org_description' => '',
            'org_phone' => '',
            'org_email' => '',
            'org_address' => '',
            'org_city' => '',
            'org_country' => '',
            'twitter_handle' => '',
            'default_og_image' => '',
            'google_analytics_id' => '',
            'google_tag_manager_id' => '',
            'facebook_pixel_id' => '',
            'tiktok_pixel_id' => '',
            'google_site_verification' => '',
            'bing_site_verification' => '',
            'robots_index' => true,
            'robots_follow' => true,
        ], $this->seo_config ?? []);
    }
}
