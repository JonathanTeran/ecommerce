<?php

use App\Services\SriInvoiceTotalsValidator;

it('validates totals against details', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="2.1.0">
  <infoFactura>
    <totalSinImpuestos>2.00</totalSinImpuestos>
    <totalDescuento>0.00</totalDescuento>
    <totalConImpuestos>
      <totalImpuesto>
        <codigo>2</codigo>
        <codigoPorcentaje>4</codigoPorcentaje>
        <baseImponible>2.00</baseImponible>
        <valor>0.30</valor>
      </totalImpuesto>
    </totalConImpuestos>
    <propina>0.00</propina>
    <importeTotal>2.30</importeTotal>
  </infoFactura>
  <detalles>
    <detalle>
      <cantidad>2.00</cantidad>
      <precioUnitario>1.00</precioUnitario>
      <descuento>0.00</descuento>
      <precioTotalSinImpuesto>2.00</precioTotalSinImpuesto>
      <impuestos>
        <impuesto>
          <codigo>2</codigo>
          <codigoPorcentaje>4</codigoPorcentaje>
          <tarifa>15.00</tarifa>
          <baseImponible>2.00</baseImponible>
          <valor>0.30</valor>
        </impuesto>
      </impuestos>
    </detalle>
  </detalles>
</factura>
XML;

    $validator = new SriInvoiceTotalsValidator();
    $result = $validator->validate($xml);

    expect($result['valid'])->toBeTrue();
});

it('fails when totals do not match', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="2.1.0">
  <infoFactura>
    <totalSinImpuestos>2.00</totalSinImpuestos>
    <totalDescuento>0.00</totalDescuento>
    <totalConImpuestos>
      <totalImpuesto>
        <codigo>2</codigo>
        <codigoPorcentaje>4</codigoPorcentaje>
        <baseImponible>2.00</baseImponible>
        <valor>0.30</valor>
      </totalImpuesto>
    </totalConImpuestos>
    <propina>0.00</propina>
    <importeTotal>2.00</importeTotal>
  </infoFactura>
  <detalles>
    <detalle>
      <cantidad>2.00</cantidad>
      <precioUnitario>1.00</precioUnitario>
      <descuento>0.00</descuento>
      <precioTotalSinImpuesto>2.00</precioTotalSinImpuesto>
      <impuestos>
        <impuesto>
          <codigo>2</codigo>
          <codigoPorcentaje>4</codigoPorcentaje>
          <tarifa>15.00</tarifa>
          <baseImponible>2.00</baseImponible>
          <valor>0.30</valor>
        </impuesto>
      </impuestos>
    </detalle>
  </detalles>
</factura>
XML;

    $validator = new SriInvoiceTotalsValidator();
    $result = $validator->validate($xml);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'])->not()->toBeEmpty();
});
