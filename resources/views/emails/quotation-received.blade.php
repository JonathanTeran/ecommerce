@extends('emails.layout')

@section('title', 'Cotización Recibida')

@section('content')
    <h2>Cotización Recibida</h2>
    <p>Hola {{ $quotation->customer_name }}, hemos recibido tu solicitud de cotización.</p>

    <div class="highlight-box">
        <p class="label">Número de Cotización</p>
        <p class="value">{{ $quotation->quotation_number }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin: 20px 0;">
        <tr>
            <td style="text-align: right; padding: 6px 0; color: #6b7280; font-size: 14px;">Subtotal</td>
            <td style="text-align: right; padding: 6px 0; font-size: 14px; width: 120px;">${{ number_format($quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 6px 0; color: #6b7280; font-size: 14px;">IVA</td>
            <td style="text-align: right; padding: 6px 0; font-size: 14px;">${{ number_format($quotation->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">Total</td>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">${{ number_format($quotation->total, 2) }}</td>
        </tr>
    </table>

    <div class="alert-success">
        <p><strong>Validez:</strong> Esta cotización es válida hasta el {{ $quotation->valid_until?->format('d/m/Y') ?? 'N/A' }}.</p>
    </div>

    <p>Nuestro equipo revisará tu solicitud y te responderemos a la brevedad posible.</p>
@endsection
