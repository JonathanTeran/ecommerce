<!DOCTYPE html>
<html>

<head>
    <title>Imprimir Código de Barras - {{ $product->sku }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .barcode-container {
            text-align: center;
            border: 1px solid #ccc;
            padding: 20px;
            background: white;
        }

        .product-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .barcode {
            margin: 10px 0;
        }

        .sku {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .barcode-container {
                border: none;
            }

            button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="barcode-container">
        <div class="product-name">{{ $product->name }}</div>
        <div class="barcode">
            {!! new \Picqer\Barcode\BarcodeGeneratorHTML()->getBarcode(
                $product->sku,
                \Picqer\Barcode\BarcodeGeneratorHTML::TYPE_CODE_128,
                2,
                60,
            ) !!}
        </div>
        <div class="sku">{{ $product->sku }}</div>
        <div style="margin-top: 20px;">
            <button onclick="window.print()">Imprimir</button>
        </div>
    </div>
</body>

</html>
