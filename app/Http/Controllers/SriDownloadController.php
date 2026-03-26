<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SriDownloadController extends Controller
{
    public function downloadXml(Order $order): StreamedResponse
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        $path = $order->sri_authorized_xml_path ?: $order->sri_xml_path;

        if (! $path) {
            abort(404);
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $filename = 'factura-'.($order->sri_access_key ?? $order->order_number).'.xml';

        return $disk->download($path, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function downloadRide(Order $order): Response
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        $filename = 'ride-'.($order->sri_access_key ?? $order->order_number).'.pdf';

        return Pdf::loadView('orders.ride', [
            'order' => $order,
            'renderForPdf' => true,
        ])
            ->setPaper('a4')
            ->download($filename);
    }
}
