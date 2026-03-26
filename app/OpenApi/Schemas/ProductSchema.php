<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductListItem',
    title: 'Product List Item',
    description: 'Producto resumido para listados',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Smartphone XYZ'),
        new OA\Property(property: 'slug', type: 'string', example: 'smartphone-xyz'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 299.99),
        new OA\Property(property: 'formatted_price', type: 'string', example: '$299.99'),
        new OA\Property(property: 'compare_price', type: 'number', format: 'float', nullable: true, example: 399.99),
        new OA\Property(property: 'discount_percentage', type: 'integer', nullable: true, example: 25),
        new OA\Property(property: 'is_new', type: 'boolean', example: true),
        new OA\Property(property: 'image_url', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'category_name', type: 'string', nullable: true, example: 'Electrónicos'),
        new OA\Property(property: 'url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProductSearchItem',
    title: 'Product Search Item',
    description: 'Producto en resultados de búsqueda',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Smartphone XYZ'),
        new OA\Property(property: 'slug', type: 'string', example: 'smartphone-xyz'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 299.99),
        new OA\Property(property: 'formatted_price', type: 'string', example: '$299.99'),
        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'Electrónicos'),
        new OA\Property(property: 'image_url', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProductRecommendation',
    title: 'Product Recommendation',
    description: 'Producto recomendado',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Funda Protectora'),
        new OA\Property(property: 'slug', type: 'string', example: 'funda-protectora'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 15.99),
        new OA\Property(property: 'formatted_price', type: 'string', example: '$15.99'),
        new OA\Property(property: 'image_url', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
class ProductSchema {}
