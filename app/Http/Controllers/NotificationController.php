<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Notificaciones', description: 'Gestión de notificaciones del usuario')]
class NotificationController extends Controller
{
    #[OA\Get(
        path: '/notifications',
        summary: 'Listar notificaciones',
        description: 'Retorna las notificaciones del usuario autenticado, paginadas.',
        tags: ['Notificaciones'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Notificaciones paginadas'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return response()->json($notifications);
    }

    #[OA\Post(
        path: '/notifications/{id}/read',
        summary: 'Marcar como leída',
        description: 'Marca una notificación específica como leída.',
        tags: ['Notificaciones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la notificación', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notificación marcada como leída'),
        ]
    )]
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['message' => 'Notificación marcada como leída.']);
    }

    #[OA\Post(
        path: '/notifications/read-all',
        summary: 'Marcar todas como leídas',
        description: 'Marca todas las notificaciones no leídas como leídas.',
        tags: ['Notificaciones'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Todas marcadas como leídas'),
        ]
    )]
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas.']);
    }

    #[OA\Get(
        path: '/notifications/unread-count',
        summary: 'Conteo de no leídas',
        description: 'Retorna el número de notificaciones no leídas.',
        tags: ['Notificaciones'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Conteo', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'count', type: 'integer', example: 5)]
            )),
        ]
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()
            ->unreadNotifications()
            ->count();

        return response()->json(['count' => $count]);
    }
}
