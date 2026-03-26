@extends('emails.layout')

@section('title', 'Cotización Rechazada')

@section('content')
    <h2>Cotización Rechazada</h2>
    <p>Hola {{ $quotation->customer_name }}, lamentamos informarte que tu cotización no ha sido aprobada.</p>

    <div class="highlight-box">
        <p class="label">Número de Cotización</p>
        <p class="value">{{ $quotation->quotation_number }}</p>
    </div>

    @if($quotation->rejection_reason)
        <div class="alert-danger">
            <p><strong>Razón:</strong> {{ $quotation->rejection_reason }}</p>
        </div>
    @endif

    @if($quotation->items && $quotation->items->count() > 0)
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="padding: 8px 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Producto</th>
                    <th style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e5e7eb;">Cant.</th>
                    <th style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e5e7eb;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                    <tr>
                        <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb;">{{ $item->name }}</td>
                        <td style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ $item->quantity }}</td>
                        <td style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e5e7eb;">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding: 8px 12px; text-align: right; font-weight: bold;">Total:</td>
                    <td style="padding: 8px 12px; text-align: right; font-weight: bold;">${{ number_format($quotation->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <p>Si tienes preguntas, no dudes en contactarnos. Puedes solicitar una nueva cotización desde la tienda.</p>
@endsection
