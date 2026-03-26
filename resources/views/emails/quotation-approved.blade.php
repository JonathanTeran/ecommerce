@extends('emails.layout')

@section('title', 'Cotización Aprobada')

@section('content')
    <h2>Cotización Aprobada</h2>
    <p>Hola {{ $quotation->customer_name }}, tu cotización ha sido aprobada.</p>

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
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">Total Aprobado</td>
            <td style="text-align: right; padding: 12px 0 6px; border-top: 2px solid #1e40af; color: #1e40af; font-size: 18px; font-weight: 700;">${{ number_format($quotation->total, 2) }}</td>
        </tr>
    </table>

    <div class="alert-success">
        <p>Tu cotización ha sido aprobada y es válida hasta el <strong>{{ $quotation->valid_until?->format('d/m/Y') ?? 'N/A' }}</strong>.</p>
    </div>

    <p>Puedes revisar los detalles de tu cotización desde tu cuenta.</p>
@endsection
