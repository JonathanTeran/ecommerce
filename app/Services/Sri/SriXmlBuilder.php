<?php

namespace App\Services\Sri;

use App\Models\GeneralSetting;
use App\Models\Order;
use SimpleXMLElement;

class SriXmlBuilder
{
    public function __construct(
        protected string $environment,
        protected string $ruc,
        protected string $companyName,
        protected string $commercialName,
        protected string $address,
        protected string $contabilidad,
        protected string $codDoc,
        protected string $estab,
        protected string $ptoEmi,
        protected string $invoiceVersion,
    ) {}

    /**
     * Generate Factura XML
     */
    public function generateInvoiceXml(Order $order, string $accessKey): string
    {
        // 1. Structure - SRI Factura version from config
        $xml = new SimpleXMLElement(sprintf(
            '<?xml version="1.0" encoding="UTF-8"?><factura id="comprobante" version="%s"></factura>',
            $this->invoiceVersion
        ));

        // 2. InfoTributaria
        $infoTributaria = $xml->addChild('infoTributaria');
        $infoTributaria->addChild('ambiente', $this->environment);
        $infoTributaria->addChild('tipoEmision', '1');
        $infoTributaria->addChild('razonSocial', htmlspecialchars($this->companyName, ENT_XML1, 'UTF-8'));
        $infoTributaria->addChild('nombreComercial', htmlspecialchars($this->commercialName, ENT_XML1, 'UTF-8'));
        $infoTributaria->addChild('ruc', $this->ruc);
        $infoTributaria->addChild('claveAcceso', $accessKey);
        $infoTributaria->addChild('codDoc', $this->codDoc);
        $infoTributaria->addChild('estab', $this->estab);
        $infoTributaria->addChild('ptoEmi', $this->ptoEmi);
        // Sequential is at offset 30 in access key (after date[8]+codDoc[2]+ruc[13]+env[1]+estab[3]+ptoEmi[3])
        $infoTributaria->addChild('secuencial', substr($accessKey, 30, 9));
        $infoTributaria->addChild('dirMatriz', htmlspecialchars($this->address, ENT_XML1, 'UTF-8'));

        // 3. InfoFactura
        $infoFactura = $xml->addChild('infoFactura');
        $infoFactura->addChild('fechaEmision', $order->created_at->format('d/m/Y'));
        $infoFactura->addChild('dirEstablecimiento', htmlspecialchars($this->address, ENT_XML1, 'UTF-8'));
        $infoFactura->addChild('obligadoContabilidad', $this->contabilidad);

        $billing = $order->billing_address ?? $order->shipping_address;
        $buyerTaxId = $billing['tax_id'] ?? ($billing['identity_document'] ?? '');
        $buyerName = $billing['name'] ?? 'CONSUMIDOR FINAL';
        $buyerAddress = trim(($billing['address'] ?? '').' '.($billing['city'] ?? ''));

        // Determine buyer ID type: 04=RUC(13), 05=Cedula(10), 06=Pasaporte, 07=Consumidor Final
        if (empty($buyerTaxId) || $buyerTaxId === '9999999999999') {
            $idType = '07'; // Consumidor Final
            $buyerTaxId = '9999999999999';
        } elseif (strlen($buyerTaxId) === 13) {
            $idType = '04'; // RUC
        } elseif (strlen($buyerTaxId) === 10) {
            $idType = '05'; // Cedula
        } else {
            $idType = '06'; // Pasaporte
        }

        $infoFactura->addChild('tipoIdentificacionComprador', $idType);
        $infoFactura->addChild('razonSocialComprador', htmlspecialchars($buyerName, ENT_XML1, 'UTF-8'));
        $infoFactura->addChild('identificacionComprador', $buyerTaxId);
        if (! empty($buyerAddress)) {
            $infoFactura->addChild('direccionComprador', htmlspecialchars($buyerAddress, ENT_XML1, 'UTF-8'));
        }
        $infoFactura->addChild('totalSinImpuestos', number_format($order->subtotal, 2, '.', ''));
        $infoFactura->addChild('totalDescuento', number_format($order->discount_amount ?? 0, 2, '.', ''));

        // Taxes Summary
        $totalImpuestos = $infoFactura->addChild('totalConImpuestos');
        $totalImpuesto = $totalImpuestos->addChild('totalImpuesto');
        $totalImpuesto->addChild('codigo', '2'); // IVA
        $totalImpuesto->addChild('codigoPorcentaje', '4'); // 15% IVA (SRI code 4 = 15%)
        $totalImpuesto->addChild('baseImponible', number_format($order->subtotal, 2, '.', ''));
        $totalImpuesto->addChild('valor', number_format($order->tax_amount ?? 0, 2, '.', ''));

        $infoFactura->addChild('propina', '0.00');
        $infoFactura->addChild('importeTotal', number_format($order->total, 2, '.', ''));
        $currency = strtoupper($order->currency ?? 'USD');
        $infoFactura->addChild('moneda', $currency);

        // Payments - formaPago (SRI):
        // 01 Sin utilizacion del sistema financiero
        // 16 Tarjeta de debito
        // 17 Dinero electronico
        // 19 Tarjeta de credito
        // 20 Otros con utilizacion del sistema financiero
        $formaPago = match ($order->payment_method?->value) {
            'cash_on_delivery' => '01',
            'debit_card' => '16',
            'payphone' => '17',
            'credit_card' => '19',
            'paypal' => '20',
            'bank_transfer' => '20',
            default => '20',
        };

        $pagos = $infoFactura->addChild('pagos');
        $pago = $pagos->addChild('pago');
        $pago->addChild('formaPago', $formaPago);
        $pago->addChild('total', number_format($order->total, 2, '.', ''));
        $pago->addChild('plazo', '0');
        $pago->addChild('unidadTiempo', 'dias');

        // 4. Detalles
        $detalles = $xml->addChild('detalles');
        foreach ($order->items as $item) {
            $detalle = $detalles->addChild('detalle');
            $detalle->addChild('codigoPrincipal', $item->sku ?: ('PROD-'.str_pad($item->product_id, 5, '0', STR_PAD_LEFT)));
            $detalle->addChild('descripcion', htmlspecialchars($item->name ?? 'Producto', ENT_XML1, 'UTF-8'));
            $detalle->addChild('cantidad', number_format($item->quantity, 2, '.', ''));
            $detalle->addChild('precioUnitario', number_format($item->price, 2, '.', ''));
            $detalle->addChild('descuento', '0.00');
            $detalle->addChild('precioTotalSinImpuesto', number_format($item->subtotal, 2, '.', ''));

            $impuestos = $detalle->addChild('impuestos');
            $impuesto = $impuestos->addChild('impuesto');
            $taxRate = GeneralSetting::getTaxRateDecimal();
            $taxPercentage = GeneralSetting::getTaxRatePercentage();
            $impuesto->addChild('codigo', '2'); // IVA
            $impuesto->addChild('codigoPorcentaje', GeneralSetting::getSriTaxCode());
            $impuesto->addChild('tarifa', number_format($taxPercentage, 2, '.', ''));
            $impuesto->addChild('baseImponible', number_format($item->subtotal, 2, '.', ''));
            $impuesto->addChild('valor', number_format($item->subtotal * $taxRate, 2, '.', ''));
        }

        return $xml->asXML();
    }
}
