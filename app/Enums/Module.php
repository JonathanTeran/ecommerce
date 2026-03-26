<?php

namespace App\Enums;

enum Module: string
{
    case Products = 'products';
    case Categories = 'categories';
    case Brands = 'brands';
    case Orders = 'orders';
    case Cart = 'cart';
    case Quotations = 'quotations';
    case Storefront = 'storefront';
    case Inventory = 'inventory';
    case Coupons = 'coupons';
    case Reviews = 'reviews';
    case Banners = 'banners';
    case Reports = 'reports';
    case SriInvoicing = 'sri_invoicing';
    case PaymentGateways = 'payment_gateways';
    case Api = 'api';
    case Returns = 'returns';
    case Taxes = 'taxes';
    case Shipping = 'shipping';
    case EmailMarketing = 'email_marketing';
    case Bundles = 'bundles';
    case Support = 'support';
    case Webhooks = 'webhooks';
    case CustomAttributes = 'custom_attributes';
    case Loyalty = 'loyalty';

    public function label(): string
    {
        return match ($this) {
            self::Products => 'Productos',
            self::Categories => 'Categorias',
            self::Brands => 'Marcas',
            self::Orders => 'Pedidos',
            self::Cart => 'Carrito de Compras',
            self::Quotations => 'Cotizaciones Online',
            self::Storefront => 'Tienda Online',
            self::Inventory => 'Gestion de Inventario',
            self::Coupons => 'Cupones y Descuentos',
            self::Reviews => 'Reviews y Valoraciones',
            self::Banners => 'Banners Promocionales',
            self::Reports => 'Reportes y Dashboards',
            self::SriInvoicing => 'Facturacion Electronica SRI',
            self::PaymentGateways => 'Pasarelas de Pago',
            self::Api => 'API REST',
            self::Returns => 'Devoluciones y RMA',
            self::Taxes => 'Gestion de Impuestos',
            self::Shipping => 'Gestion de Envios',
            self::EmailMarketing => 'Email Marketing',
            self::Bundles => 'Bundles y Kits',
            self::Support => 'Soporte y Tickets',
            self::Webhooks => 'Webhooks',
            self::CustomAttributes => 'Atributos Personalizados',
            self::Loyalty => 'Programa de Fidelidad',
        };
    }
}
