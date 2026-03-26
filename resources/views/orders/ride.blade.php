<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RIDE - {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #111;
            font-size: 11px;
            line-height: 1.4;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .header-box {
            border: 1px solid #111;
            padding: 8px;
            vertical-align: top;
        }

        .logo {
            max-width: 190px;
            max-height: 90px;
        }

        .company-title {
            font-weight: bold;
            font-size: 12px;
            margin-top: 6px;
        }

        .doc-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            width: 140px;
            padding-right: 6px;
        }

        .value {
            font-size: 11px;
        }

        .sri-info td {
            padding: 2px 0;
        }

        .access-title {
            text-align: center;
            font-weight: bold;
            margin-top: 6px;
        }

        .barcode {
            text-align: center;
            margin-top: 4px;
        }

        .barcode img {
            width: 260px;
            height: 60px;
        }

        .barcode-text {
            font-size: 10px;
            letter-spacing: 1px;
        }

        .buyer-table td {
            border: 1px solid #111;
            padding: 4px 6px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #111;
            padding: 3px 4px;
            font-size: 10px;
        }

        .items-table th {
            background: #f3f3f3;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-table td {
            border: 1px solid #111;
            padding: 3px 4px;
            font-size: 10px;
        }

        .totals-table .total-label {
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .additional-box {
            border: 1px solid #111;
            padding: 6px;
            font-size: 10px;
        }

        .pay-table td {
            border: 1px solid #111;
            padding: 3px 4px;
            font-size: 10px;
        }

        .pay-table th {
            border: 1px solid #111;
            padding: 3px 4px;
            font-size: 10px;
            background: #f3f3f3;
            text-transform: uppercase;
        }

        .page-footer {
            margin-top: 10px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        $renderForPdf = $renderForPdf ?? false;
        $settings = \App\Models\GeneralSetting::first();
        $companyName = $settings?->sri_company_name ?? $settings?->site_name ?? 'Empresa';
        $commercialName = $settings?->sri_commercial_name ?? $companyName;
        $address = $settings?->sri_establishment_address ?? 'Ecuador';
        $accounting = ($settings?->sri_accounting_required ?? false) ? 'SI' : 'NO';
        $contribution = $settings?->sri_contribution_type;
        $ruc = $settings?->sri_ruc ?? '9999999999999';

        $accessKey = $order->sri_access_key ?? 'N/D';
        $estab = $settings?->sri_establishment_code ?? '001';
        $ptoEmi = $settings?->sri_emission_point_code ?? '001';
        $secuencial = str_pad($order->id, 9, '0', STR_PAD_LEFT);

        if (is_string($accessKey) && strlen($accessKey) >= 39) {
            $estab = substr($accessKey, 24, 3);
            $ptoEmi = substr($accessKey, 27, 3);
            $secuencial = substr($accessKey, 30, 9);
        }

        $invoiceNumber = $estab.'-'.$ptoEmi.'-'.$secuencial;
        $authNumber = $order->sri_authorization_number ?? 'Pendiente';
        $authDate = $order->sri_authorization_date
            ? \Illuminate\Support\Carbon::parse($order->sri_authorization_date)->format('d/m/Y H:i')
            : 'Pendiente';
        $environment = ($settings?->sri_environment ?? 1) === 2 ? 'Produccion' : 'Pruebas';
        $emission = 'Normal';

        $paymentLabel = $order->payment_method?->getLabel() ?? $order->paymentMethod?->name ?? 'N/D';

        $logoSrc = null;
        if ($settings?->site_logo) {
            $logoPath = public_path('storage/'.$settings->site_logo);
            if ($renderForPdf && file_exists($logoPath)) {
                $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION)) ?: 'png';
                $logoSrc = 'data:image/'.$extension.';base64,'.base64_encode(file_get_contents($logoPath));
            } else {
                $logoSrc = asset('storage/'.$settings->site_logo);
            }
        }

        $barcodeSrc = null;
        if (is_string($accessKey) && $accessKey !== 'N/D') {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeSrc = 'data:image/png;base64,'.base64_encode($generator->getBarcode($accessKey, $generator::TYPE_CODE_128, 2, 60));
        }
    @endphp

    @if (! $renderForPdf)
        <div style="text-align: center; margin-bottom: 10px;">
            <button onclick="window.print()">Imprimir RIDE</button>
        </div>
    @endif

    <table>
        <tr>
            <td class="header-box" style="width: 55%;">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                @endif

                <div class="company-title">{{ $commercialName }}</div>
                <div>{{ $companyName }}</div>
                <div>Dir. Matriz: {{ $address }}</div>
                <div>Dir. Sucursal: {{ $address }}</div>
                <div>Obligado a llevar contabilidad: {{ $accounting }}</div>
                @if ($contribution)
                    <div>{{ $contribution }}</div>
                @endif
            </td>
            <td class="header-box" style="width: 45%;">
                <div class="doc-title">FACTURA</div>
                <table class="sri-info">
                    <tr>
                        <td class="label">R.U.C.</td>
                        <td class="value">{{ $ruc }}</td>
                    </tr>
                    <tr>
                        <td class="label">No.</td>
                        <td class="value">{{ $invoiceNumber }}</td>
                    </tr>
                    <tr>
                        <td class="label">Numero Autorizacion</td>
                        <td class="value">{{ $authNumber }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha y Hora de Autorizacion</td>
                        <td class="value">{{ $authDate }}</td>
                    </tr>
                    <tr>
                        <td class="label">Ambiente</td>
                        <td class="value">{{ $environment }}</td>
                    </tr>
                    <tr>
                        <td class="label">Emision</td>
                        <td class="value">{{ $emission }}</td>
                    </tr>
                </table>

                <div class="access-title">CLAVE DE ACCESO</div>
                <div class="barcode">
                    @if ($barcodeSrc)
                        <img src="{{ $barcodeSrc }}" alt="Barcode">
                    @endif
                    <div class="barcode-text">{{ $accessKey }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="buyer-table" style="margin-top: 8px;">
        <tr>
            <td style="width: 65%;"><strong>Razon Social / Nombres y Apellidos:</strong> {{ $order->billing_address['name'] ?? $order->user->name }}</td>
            <td style="width: 35%;"><strong>RUC / CI:</strong> {{ $order->billing_address['tax_id'] ?? ($order->billing_address['identity_document'] ?? '') }}</td>
        </tr>
        <tr>
            <td><strong>Fecha Emision:</strong> {{ $order->created_at->format('d/m/Y') }}</td>
            <td><strong>Guia de Remision:</strong></td>
        </tr>
    </table>

    <table class="items-table" style="margin-top: 8px;">
        <thead>
            <tr>
                <th style="width: 12%;">Cod. Principal</th>
                <th style="width: 10%;">Cod. Auxiliar</th>
                <th style="width: 6%;">Cant.</th>
                <th style="width: 30%;">Descripcion</th>
                <th style="width: 14%;">Detalle Adicional</th>
                <th style="width: 10%;">Precio Unitario</th>
                <th style="width: 8%;">Descuento</th>
                <th style="width: 10%;">Precio Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php
                    $detail = [];
                    if ($item->variant) {
                        $detail[] = $item->variant->name;
                    }
                    if (! empty($item->options)) {
                        foreach ($item->options as $key => $val) {
                            $detail[] = $key.': '.$val;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $item->sku ?? $item->product->sku ?? ('PROD-'.str_pad($item->product_id, 5, '0', STR_PAD_LEFT)) }}</td>
                    <td></td>
                    <td class="text-center">{{ number_format($item->quantity, 2, '.', '') }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ implode(' | ', $detail) }}</td>
                    <td class="text-right">${{ number_format($item->price, 2) }}</td>
                    <td class="text-right">$0.00</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="section-title">Forma de Pago</div>
                <table class="pay-table">
                    <thead>
                        <tr>
                            <th style="width: 55%;">Forma de Pago</th>
                            <th style="width: 20%;">Valor</th>
                            <th style="width: 10%;">Plazo</th>
                            <th style="width: 15%;">Tiempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $paymentLabel }}</td>
                            <td class="text-right">${{ number_format($order->total, 2) }}</td>
                            <td class="text-center">0</td>
                            <td class="text-center">dias</td>
                        </tr>
                    </tbody>
                </table>

                <div class="section-title" style="margin-top: 8px;">Informacion Adicional</div>
                <div class="additional-box">
                    <div><strong>Direccion:</strong> {{ $order->billing_address['address'] ?? '' }}</div>
                    <div><strong>Telefono:</strong> {{ $order->billing_address['phone'] ?? '' }}</div>
                    <div><strong>Email:</strong> {{ $order->user->email ?? '' }}</div>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td>SUBTOTAL {{ \App\Models\GeneralSetting::getTaxRatePercentage() }}%</td>
                        <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>SUBTOTAL 0%</td>
                        <td class="text-right">$0.00</td>
                    </tr>
                    <tr>
                        <td>SUBTOTAL NO OBJETO IVA</td>
                        <td class="text-right">$0.00</td>
                    </tr>
                    <tr>
                        <td>SUBTOTAL EXENTO IVA</td>
                        <td class="text-right">$0.00</td>
                    </tr>
                    <tr>
                        <td>SUBTOTAL SIN IMPUESTOS</td>
                        <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>DESCUENTO</td>
                        <td class="text-right">${{ number_format($order->discount_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>ICE</td>
                        <td class="text-right">$0.00</td>
                    </tr>
                    <tr>
                        <td>IVA {{ \App\Models\GeneralSetting::getTaxRatePercentage() }}%</td>
                        <td class="text-right">${{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>PROPINA</td>
                        <td class="text-right">$0.00</td>
                    </tr>
                    <tr>
                        <td class="total-label">VALOR TOTAL</td>
                        <td class="text-right total-label">${{ number_format($order->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="total-label">VALOR A PAGAR</td>
                        <td class="text-right total-label">${{ number_format($order->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="page-footer">Pagina 1 de 1</div>
</body>

</html>
