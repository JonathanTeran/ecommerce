<?php

namespace App\Enums;

enum PlanType: string
{
    case Basic = 'basic';
    case Professional = 'professional';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basico',
            self::Professional => 'Profesional',
            self::Enterprise => 'Enterprise',
        };
    }

    public function price(): float
    {
        return match ($this) {
            self::Basic => 50.00,
            self::Professional => 100.00,
            self::Enterprise => 150.00,
        };
    }

    public function maxProducts(): ?int
    {
        return match ($this) {
            self::Basic => 500,
            self::Professional => null,
            self::Enterprise => null,
        };
    }

    public function maxUsers(): ?int
    {
        return match ($this) {
            self::Basic => 1,
            self::Professional => 3,
            self::Enterprise => null,
        };
    }

    /**
     * @return Module[]
     */
    public function modules(): array
    {
        return match ($this) {
            self::Basic => [
                Module::Products,
                Module::Categories,
                Module::Brands,
                Module::Orders,
                Module::Cart,
                Module::Quotations,
                Module::Storefront,
            ],
            self::Professional => [
                Module::Products,
                Module::Categories,
                Module::Brands,
                Module::Orders,
                Module::Cart,
                Module::Quotations,
                Module::Storefront,
                Module::Inventory,
                Module::Coupons,
                Module::Reviews,
                Module::Banners,
                Module::Reports,
            ],
            self::Enterprise => Module::cases(),
        };
    }

    public function hasModule(Module $module): bool
    {
        return in_array($module, $this->modules());
    }
}
