<?php

namespace App\Enums;

enum WebhookEvent: string
{
    case OrderCreated = 'order.created';
    case OrderStatusChanged = 'order.status_changed';
    case PaymentCompleted = 'payment.completed';
    case ProductCreated = 'product.created';
    case ProductUpdated = 'product.updated';
    case StockLow = 'stock.low';

    public function label(): string
    {
        return match ($this) {
            self::OrderCreated => 'Orden Creada',
            self::OrderStatusChanged => 'Cambio de Estado de Orden',
            self::PaymentCompleted => 'Pago Completado',
            self::ProductCreated => 'Producto Creado',
            self::ProductUpdated => 'Producto Actualizado',
            self::StockLow => 'Stock Bajo',
        };
    }
}
