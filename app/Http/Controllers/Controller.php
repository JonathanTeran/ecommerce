<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Ecommerce SaaS API',
    description: 'API REST para la plataforma ecommerce multi-tenant SaaS. Incluye endpoints para catálogo, carrito, checkout, recomendaciones y cotizaciones.',
    contact: new OA\Contact(name: 'Soporte API', email: 'api@ecommerce.test'),
)]
#[OA\Server(url: '/api', description: 'API Server')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'apiKey',
    name: 'Authorization',
    in: 'header',
    description: 'Token Sanctum. Formato: Bearer {token}'
)]
abstract class Controller
{
    //
}
