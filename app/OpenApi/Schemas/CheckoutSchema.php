<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CheckoutRequest',
    title: 'Checkout / Place Order Request',
    description: 'Datos para colocar una orden',
    required: ['payment_method', 'shipping_address', 'billing_address'],
    properties: [
        new OA\Property(property: 'payment_method', type: 'string', example: 'transfer', description: 'Método de pago (transfer, cash_on_delivery, nuvei, payphone, kushki)'),
        new OA\Property(property: 'shipping_address', type: 'object', description: 'Dirección de envío'),
        new OA\Property(property: 'billing_address', type: 'object', description: 'Dirección de facturación'),
        new OA\Property(property: 'shipping_rate_key', type: 'string', nullable: true, description: 'Clave de tarifa de envío'),
        new OA\Property(property: 'payment_proof', type: 'string', format: 'binary', nullable: true, description: 'Comprobante de pago (para transferencia)'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'OrderResponse',
    title: 'Order Response',
    description: 'Respuesta al crear una orden',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Order placed successfully'),
        new OA\Property(property: 'redirect', type: 'string', format: 'uri', description: 'URL de redirección'),
        new OA\Property(property: 'order_id', type: 'integer', example: 42),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'QuotationRequest',
    title: 'Quotation Request',
    description: 'Datos para solicitar una cotización',
    required: ['shipping_address'],
    properties: [
        new OA\Property(property: 'shipping_address', type: 'object', description: 'Dirección de envío'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, description: 'Notas adicionales'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'QuotationResponse',
    title: 'Quotation Response',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Cotización enviada correctamente'),
        new OA\Property(property: 'redirect', type: 'string', format: 'uri'),
        new OA\Property(property: 'quotation_id', type: 'integer', example: 7),
        new OA\Property(property: 'quotation_number', type: 'string', example: 'QT-ABC12345'),
    ],
    type: 'object'
)]
class CheckoutSchema {}
