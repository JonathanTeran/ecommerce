<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 3px solid #2563eb; padding-bottom: 20px; }
        .header-left { }
        .header-right { text-align: right; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e40af; margin-bottom: 5px; }
        .quotation-title { font-size: 24px; font-weight: bold; color: #1e40af; margin-bottom: 5px; }
        .quotation-number { font-size: 14px; color: #6b7280; }
        .info-grid { width: 100%; margin-bottom: 30px; }
        .info-grid td { vertical-align: top; padding: 0; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; }
        .info-box h3 { font-size: 11px; text-transform: uppercase; color: #6b7280; margin: 0 0 8px 0; letter-spacing: 0.5px; }
        .info-box p { margin: 3px 0; font-size: 12px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead th { background: #1e40af; color: white; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.items thead th:last-child, table.items thead th:nth-child(3), table.items thead th:nth-child(4) { text-align: right; }
        table.items tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        table.items tbody td:last-child, table.items tbody td:nth-child(3), table.items tbody td:nth-child(4) { text-align: right; }
        .totals { width: 300px; margin-left: auto; margin-bottom: 30px; }
        .totals table { width: 100%; }
        .totals td { padding: 6px 0; }
        .totals .label { color: #6b7280; }
        .totals .value { text-align: right; font-weight: bold; }
        .totals .total-row { border-top: 2px solid #1e40af; font-size: 16px; color: #1e40af; }
        .totals .total-row td { padding-top: 10px; }
        .validity { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px 15px; margin-bottom: 20px; text-align: center; }
        .validity strong { color: #92400e; }
        .notes { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .notes h3 { font-size: 11px; text-transform: uppercase; color: #6b7280; margin: 0 0 8px 0; }
        .footer { text-align: center; color: #9ca3af; font-size: 10px; margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-converted { background: #dbeafe; color: #1e40af; }
        .status-expired { background: #f3f4f6; color: #374151; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>
    {{-- Header --}}
    <table style="width: 100%; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 15px;">
        <tr>
            <td style="width: 50%;">
                <div class="company-name">{{ $settings?->site_name ?? config('app.name') }}</div>
                @if($settings?->sri_company_name)
                    <p style="margin: 2px 0; color: #6b7280;">{{ $settings->sri_company_name }}</p>
                @endif
                @if($settings?->sri_establishment_address)
                    <p style="margin: 2px 0; color: #6b7280;">{{ $settings->sri_establishment_address }}</p>
                @endif
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="quotation-title">COTIZACIÓN</div>
                <div class="quotation-number">{{ $quotation->quotation_number }}</div>
                <p style="margin: 5px 0; color: #6b7280;">Fecha: {{ $quotation->created_at->format('d/m/Y') }}</p>
                <span class="status-badge status-{{ $quotation->status->value }}">
                    {{ strtoupper($quotation->status->getLabel()) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Customer Info --}}
    <table class="info-grid">
        <tr>
            <td style="width: 48%;">
                <div class="info-box">
                    <h3>Datos del Cliente</h3>
                    <p><strong>{{ $quotation->customer_name }}</strong></p>
                    <p>{{ $quotation->customer_email }}</p>
                    @if($quotation->customer_phone)<p>Tel: {{ $quotation->customer_phone }}</p>@endif
                    @if($quotation->customer_company)<p>Empresa: {{ $quotation->customer_company }}</p>@endif
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%;">
                @if($quotation->shipping_address)
                    <div class="info-box">
                        <h3>Dirección de Envío</h3>
                        <p><strong>{{ $quotation->shipping_address['name'] ?? '' }}</strong></p>
                        <p>{{ $quotation->shipping_address['address'] ?? '' }}</p>
                        <p>{{ $quotation->shipping_address['city'] ?? '' }}, {{ $quotation->shipping_address['state'] ?? '' }} {{ $quotation->shipping_address['zip'] ?? '' }}</p>
                        @if(!empty($quotation->shipping_address['phone']))<p>Tel: {{ $quotation->shipping_address['phone'] }}</p>@endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Validity --}}
    <div class="validity">
        <strong>Cotización válida hasta: {{ $quotation->valid_until?->format('d/m/Y') ?? 'N/A' }}</strong>
    </div>

    {{-- Items --}}
    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th>Cant.</th>
                <th>P. Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sku }}</td>
                    <td style="text-align: right;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">${{ number_format($item->price, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">${{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            @if($quotation->discount_amount > 0)
                <tr>
                    <td class="label">Descuento</td>
                    <td class="value">-${{ number_format($quotation->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">IVA</td>
                <td class="value">${{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label"><strong>TOTAL</strong></td>
                <td class="value"><strong>${{ number_format($quotation->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Notes --}}
    @if($quotation->customer_notes || $quotation->admin_notes)
        <div class="notes">
            @if($quotation->customer_notes)
                <h3>Notas del Cliente</h3>
                <p>{{ $quotation->customer_notes }}</p>
            @endif
            @if($quotation->admin_notes)
                <h3 style="margin-top: 10px;">Notas</h3>
                <p>{{ $quotation->admin_notes }}</p>
            @endif
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $settings?->site_name ?? config('app.name') }} | Cotización generada el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Este documento es una cotización y no constituye una factura.</p>
    </div>
</body>
</html>
