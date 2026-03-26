<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('name')
                ->label('Nombre'),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('description')
                ->label('Descripcion'),
            ExportColumn::make('short_description')
                ->label('Descripcion Corta'),
            ExportColumn::make('category.name')
                ->label('Categoria'),
            ExportColumn::make('brand.name')
                ->label('Marca'),
            ExportColumn::make('price')
                ->label('Precio'),
            ExportColumn::make('compare_price')
                ->label('Precio Comparacion'),
            ExportColumn::make('cost')
                ->label('Costo'),
            ExportColumn::make('quantity')
                ->label('Cantidad'),
            ExportColumn::make('low_stock_threshold')
                ->label('Umbral Stock Bajo'),
            ExportColumn::make('weight')
                ->label('Peso'),
            ExportColumn::make('warranty_months')
                ->label('Garantia (meses)'),
            ExportColumn::make('is_active')
                ->label('Activo')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Si' : 'No'),
            ExportColumn::make('is_featured')
                ->label('Destacado')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Si' : 'No'),
            ExportColumn::make('is_new')
                ->label('Nuevo')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Si' : 'No'),
            ExportColumn::make('views_count')
                ->label('Vistas'),
            ExportColumn::make('sales_count')
                ->label('Ventas'),
            ExportColumn::make('created_at')
                ->label('Fecha Creacion'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportacion de productos ha finalizado. '.number_format($export->successful_rows).' '.str('fila')->plural($export->successful_rows).' exportada(s).';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('fila')->plural($failedRowsCount).' fallo.';
        }

        return $body;
    }
}
