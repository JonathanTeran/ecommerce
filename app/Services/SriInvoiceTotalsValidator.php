<?php

namespace App\Services;

use SimpleXMLElement;

class SriInvoiceTotalsValidator
{
    /**
     * @return array{valid: bool, message: string, errors: array<int, string>}
     */
    public function validate(string $xml): array
    {
        $previousErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $document = new SimpleXMLElement($xml);
        } catch (\Throwable $exception) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);

            return [
                'valid' => false,
                'message' => 'XML invalido para validar totales.',
                'errors' => ['No se pudo cargar el XML para validar.'],
            ];
        }

        $errors = [];

        if (! isset($document->infoFactura) || ! isset($document->detalles)) {
            libxml_use_internal_errors($previousErrors);

            return [
                'valid' => false,
                'message' => 'XML sin infoFactura o detalles.',
                'errors' => ['No se encontro infoFactura o detalles en el XML.'],
            ];
        }

        $infoFactura = $document->infoFactura;
        $detalles = $document->detalles->detalle ?? [];

        $detailsSubtotal = 0;
        $detailsDiscount = 0;
        $detailsTaxTotal = 0;

        foreach ($detalles as $detalle) {
            $cantidad = $this->toFloat($detalle->cantidad ?? 0);
            $precioUnitario = $this->toFloat($detalle->precioUnitario ?? 0);
            $descuento = $this->toFloat($detalle->descuento ?? 0);
            $precioTotalSinImpuesto = $this->toFloat($detalle->precioTotalSinImpuesto ?? 0);

            $expectedLineBase = ($cantidad * $precioUnitario) - $descuento;

            $detailsSubtotal += $precioTotalSinImpuesto;
            $detailsDiscount += $descuento;

            if ($this->diffCents($expectedLineBase, $precioTotalSinImpuesto) > 1) {
                $errors[] = 'Detalle con total sin impuesto incorrecto.';
            }

            if (isset($detalle->impuestos->impuesto)) {
                foreach ($detalle->impuestos->impuesto as $impuesto) {
                    $base = $this->toFloat($impuesto->baseImponible ?? 0);
                    $valor = $this->toFloat($impuesto->valor ?? 0);
                    $tarifa = $this->toFloat($impuesto->tarifa ?? 0);

                    $detailsTaxTotal += $valor;

                    if ($this->diffCents($base, $expectedLineBase) > 1) {
                        $errors[] = 'Base imponible no coincide con el detalle.';
                    }

                    $expectedTax = $base * ($tarifa / 100);
                    if ($this->diffCents($expectedTax, $valor) > 1) {
                        $errors[] = 'Impuesto con valor incorrecto.';
                    }
                }
            }
        }

        $totalSinImpuestos = $this->toFloat($infoFactura->totalSinImpuestos ?? 0);
        $totalDescuento = $this->toFloat($infoFactura->totalDescuento ?? 0);
        $propina = $this->toFloat($infoFactura->propina ?? 0);
        $importeTotal = $this->toFloat($infoFactura->importeTotal ?? 0);

        $totalImpuestos = 0.0;
        if (isset($infoFactura->totalConImpuestos->totalImpuesto)) {
            foreach ($infoFactura->totalConImpuestos->totalImpuesto as $totalImpuesto) {
                $totalImpuestos += $this->toFloat($totalImpuesto->valor ?? 0);
            }
        }

        if ($this->diffCents($detailsSubtotal, $totalSinImpuestos) > 1) {
            $errors[] = 'totalSinImpuestos no coincide con los detalles.';
        }

        if ($this->diffCents($detailsDiscount, $totalDescuento) > 1) {
            $errors[] = 'totalDescuento no coincide con los detalles.';
        }

        if ($this->diffCents($detailsTaxTotal, $totalImpuestos) > 1) {
            $errors[] = 'totalConImpuestos no coincide con los detalles.';
        }

        $expectedImporteTotal = $totalSinImpuestos - $totalDescuento + $totalImpuestos + $propina;
        if ($this->diffCents($expectedImporteTotal, $importeTotal) > 1) {
            $errors[] = 'importeTotal no coincide con los totales.';
        }

        libxml_use_internal_errors($previousErrors);

        if ($errors !== []) {
            return [
                'valid' => false,
                'message' => 'Totales inconsistentes en el XML.',
                'errors' => $errors,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Totales validados correctamente.',
            'errors' => [],
        ];
    }

    protected function toFloat(mixed $value): float
    {
        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', $stringValue);
    }

    protected function diffCents(float $a, float $b): int
    {
        $aCents = (int) round($a * 100);
        $bCents = (int) round($b * 100);

        return abs($aCents - $bCents);
    }
}
