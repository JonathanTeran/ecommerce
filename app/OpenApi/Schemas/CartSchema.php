<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartResponse',
    title: 'Cart Response',
    description: 'Respuesta del carrito de compras',
    properties: [
        new OA\Property(property: 'data', type: 'object', description: 'Datos del carrito con items, subtotal, impuestos y total'),
        new OA\Property(property: 'session_id', type: 'string', description: 'ID de sesión del carrito', example: 'a1b2c3d4-e5f6-7890'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CartAddRequest',
    title: 'Add to Cart Request',
    description: 'Datos para agregar un producto al carrito',
    required: ['product_id'],
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'variant_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'options', type: 'object', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CartUpdateRequest',
    title: 'Update Cart Item Request',
    description: 'Datos para actualizar cantidad de un item',
    required: ['quantity'],
    properties: [
        new OA\Property(property: 'quantity', type: 'integer', minimum: 0, example: 2),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CouponRequest',
    title: 'Apply Coupon Request',
    required: ['code'],
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'DESCUENTO10'),
    ],
    type: 'object'
)]
class CartSchema {}
