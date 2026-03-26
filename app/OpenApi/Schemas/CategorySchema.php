<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategoryItem',
    title: 'Category Item',
    description: 'Categoría con conteo de productos',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Electrónicos'),
        new OA\Property(property: 'slug', type: 'string', example: 'electronicos'),
        new OA\Property(property: 'image_url', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'products_count', type: 'integer', example: 24),
    ],
    type: 'object'
)]
class CategorySchema {}
