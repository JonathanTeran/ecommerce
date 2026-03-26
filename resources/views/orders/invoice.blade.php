<!DOCTYPE html>
<html>

<head>
    <title>Factura - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .company-details h1 {
            color: #2c3e50;
            margin: 0 0 5px 0;
            font-size: 24px;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-details h2 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 24px;
        }

        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .addr-box {
            width: 48%;
        }

        .addr-title {
            font-weight: bold;
            text-transform: uppercase;
            color: #7f8c8d;
            font-size: 11px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            width: 300px;
            margin-left: auto;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .totals-row.grand-total {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
            border-bottom: 2px solid #2c3e50;
            margin-top: 10px;
            padding-top: 15px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #95a5a6;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        @media print {
            body {
                padding: 0;
            }

            .container {
                border: none;
                box-shadow: none;
                width: 100%;
                max-width: none;
            }

            .no-print {
                display: none;
            }
        }

        .btn-print {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimir Factura</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="company-details">
                @php
                    $settings = \App\Models\GeneralSetting::first();
                @endphp
                <h1>{{ $settings?->site_name ?? config('app.name') }}</h1>
                @if($settings?->sri_establishment_address)
                    <div>{{ $settings->sri_establishment_address }}</div>
                @endif
                @if($settings?->mail_from_address)
                    <div>Email: {{ $settings->mail_from_address }}</div>
                @endif
            </div>
            <div class="invoice-details">
                <h2>FACTURA</h2>
                <div>Nº: <strong>{{ $order->order_number }}</strong></div>
                <div>Fecha: {{ $order->created_at->format('d/m/Y') }}</div>
                <div>Estado: {{ $order->payment_status->getLabel() }}</div>
            </div>
        </div>

        <div class="addresses">
            <div class="addr-box">
                <div class="addr-title">Facturar a:</div>
                <strong>{{ $order->billing_address['name'] ?? $order->user->name }}</strong><br>
                {{ $order->billing_address['tax_id'] ?? '' }}<br>
                {{ $order->billing_address['address'] ?? '' }}<br>
                {{ $order->billing_address['city'] ?? '' }} {{ $order->billing_address['state'] ?? '' }}<br>
                {{ $order->billing_address['phone'] ?? '' }}
            </div>
            <div class="addr-box">
                <div class="addr-title">Enviar a:</div>
                <strong>{{ $order->shipping_address['name'] ?? '' }}</strong><br>
                {{ $order->shipping_address['identity_document'] ?? '' }}<br>
                {{ $order->shipping_address['address'] ?? '' }}<br>
                {{ $order->shipping_address['city'] ?? '' }} {{ $order->shipping_address['state'] ?? '' }}<br>
                {{ $order->shipping_address['phone'] ?? '' }}
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->product->name }}
                            @if ($item->variant)
                                <br><small class="text-muted">{{ $item->variant->name }}</small>
                            @endif
                            @if (!empty($item->options))
                                <br><small class="text-muted">
                                    @foreach ($item->options as $key => $val)
                                        {{ $key }}: {{ $val }}@if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if ($order->discount_amount > 0)
                <div class="totals-row">
                    <span>Descuento:</span>
                    <span>-${{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="totals-row">
                <span>IVA ({{ \App\Models\GeneralSetting::getTaxRatePercentage() }}%):</span>
                <span>${{ number_format($order->tax_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Envío:</span>
                <span>${{ number_format($order->shipping_amount, 2) }}</span>
            </div>
            <div class="totals-row grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Gracias por su compra.</p>
            <p>Si tiene alguna pregunta sobre esta factura, por favor contáctenos.</p>
        </div>
    </div>
</body>

</html>
