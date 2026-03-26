<!DOCTYPE html>
<html>

<head>
    <title>Etiqueta de Envío - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .shipping-label {
            width: 4in;
            height: 6in;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 2px solid #333;
            padding: 0;
            overflow: hidden;
        }

        /* Header with company branding */
        .label-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
        }

        .company-logo {
            max-width: 150px;
            max-height: 50px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .label-body {
            padding: 12px;
        }

        .section {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #667eea;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .section-content {
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .section-content strong {
            display: block;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .address {
            margin-top: 4px;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
        }

        .order-info div {
            flex: 1;
        }

        .barcode-section {
            text-align: center;
            padding: 10px 0;
            background: #fafafa;
            border-radius: 4px;
            margin: 8px 0;
        }

        .barcode-section .barcode {
            margin: 8px 0;
        }

        .tracking-number {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        .shipping-method {
            background: #f0f0f0;
            padding: 8px;
            text-align: center;
            border-radius: 4px;
            margin-top: 8px;
        }

        .shipping-method strong {
            font-size: 14px;
            color: #667eea;
        }

        /* Print specific styles */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .shipping-label {
                box-shadow: none;
                border: 1px solid #333;
                margin: 0;
            }

            .no-print {
                display: none;
            }

            .barcode-section {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .barcode-section .barcode svg,
            .barcode-section .barcode * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .print-button:hover {
            background: #764ba2;
        }
    </style>
</head>

<body>
    <div class="shipping-label">
        <!-- Header -->
        <div class="label-header">
            @php
                $settings = \App\Models\GeneralSetting::first();
                $companyLogo = $settings?->site_logo;
                $companyName = $settings?->site_name ?? config('app.name');
            @endphp
            @if ($companyLogo)
                <img src="{{ url('storage/' . $companyLogo) }}" alt="{{ $companyName }}" class="company-logo">
            @else
                <div class="company-name">{{ $companyName }}</div>
            @endif
        </div>

        <!-- Body -->
        <div class="label-body">
            <!-- Sender Information -->
            <div class="section">
                <div class="section-title">📦 Remitente</div>
                <div class="section-content">
                    <strong>{{ $companyName }}</strong>
                    @if($settings?->sri_establishment_address)
                        <div class="address">
                            {{ $settings->sri_establishment_address }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recipient Information -->
            <div class="section">
                <div class="section-title">👤 Destinatario</div>
                <div class="section-content">
                    <strong>{{ $order->shipping_address['name'] ?? 'N/A' }}</strong>
                    <div class="address">
                        {{ $order->shipping_address['address'] ?? '' }}
                        <br><strong>{{ $order->shipping_address['city'] ?? '' }},
                            {{ $order->shipping_address['state'] ?? '' }}</strong>
                        <br>C.P.: {{ $order->shipping_address['zip'] ?? '' }}
                        <br>Tel: {{ $order->shipping_address['phone'] ?? 'N/A' }}
                        @if (isset($order->shipping_address['identity_document']))
                            <br>CI/RUC: {{ $order->shipping_address['identity_document'] }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="section">
                <div class="order-info">
                    <div>
                        <div class="section-title">Pedido</div>
                        <div class="section-content"><strong>{{ $order->order_number }}</strong></div>
                    </div>
                    <div>
                        <div class="section-title">Fecha</div>
                        <div class="section-content">{{ $order->created_at->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <div class="section-title">Paquetes</div>
                        <div class="section-content"><strong>{{ $order->items->count() }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- Barcode -->
            <div class="barcode-section">
                <div class="barcode">
                    @php
                        $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
                    @endphp
                    {!! $generator->getBarcode($order->order_number, $generator::TYPE_CODE_128, 2, 50) !!}
                </div>
                <div class="tracking-number">{{ $order->tracking_number ?? $order->order_number }}</div>
            </div>

            <!-- Shipping Method -->
            @if ($order->shipping_method)
                <div class="shipping-method">
                    <strong>{{ $order->shipping_method }}</strong>
                </div>
            @endif
        </div>
    </div>

    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir Etiqueta</button>
</body>

</html>
