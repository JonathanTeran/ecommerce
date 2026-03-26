<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceQuotationRequest;
use App\Models\Cart;
use App\Services\QuotationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Cotizaciones', description: 'Solicitud de cotizaciones online')]
class QuotationApiController extends Controller
{
    #[OA\Post(
        path: '/quotation',
        summary: 'Solicitar cotización',
        description: 'Crea una cotización a partir del carrito actual. Requiere autenticación.',
        tags: ['Cotizaciones'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/QuotationRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cotización creada', content: new OA\JsonContent(ref: '#/components/schemas/QuotationResponse')),
            new OA\Response(response: 400, description: 'Carrito vacío'),
            new OA\Response(response: 401, description: 'Autenticación requerida'),
            new OA\Response(response: 403, description: 'Cotizaciones deshabilitadas en esta tienda'),
        ]
    )]
    public function store(PlaceQuotationRequest $request): JsonResponse
    {
        $settings = \App\Models\GeneralSetting::first();
        if ($settings && ! $settings->isQuotationsEnabled()) {
            return response()->json(['message' => 'Las cotizaciones no están disponibles en esta tienda.'], 403);
        }

        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $sessionId = $request->header('X-Session-ID') ?? $request->cookie('cart_session_id');
        $cart = null;

        $cart = Cart::forUser($user->id)->with('items.product')->first();

        if ((! $cart || $cart->items->isEmpty()) && $sessionId) {
            $sessionCart = Cart::forSession($sessionId)->with('items.product')->first();
            if ($sessionCart && $sessionCart->items->isNotEmpty()) {
                $sessionCart->update(['user_id' => $user->id]);
                $cart = $sessionCart;
            }
        }

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $validated = $request->validated();

        $service = app(QuotationService::class);

        $quotation = $service->createFromCart($cart, $user, $validated);

        return response()->json([
            'message' => 'Cotización enviada correctamente',
            'redirect' => route('quotation.confirmation', $quotation),
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
        ]);
    }
}
