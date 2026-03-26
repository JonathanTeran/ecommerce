<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('sku')
                ->label('SKU')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100']),

            ImportColumn::make('price')
                ->label('Precio')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('compare_price')
                ->label('Precio Comparacion')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('cost')
                ->label('Costo')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('description')
                ->label('Descripcion'),

            ImportColumn::make('short_description')
                ->label('Descripcion Corta'),

            ImportColumn::make('category')
                ->label('Categoria (Nombre)')
                ->relationship(resolveUsing: 'name'),

            ImportColumn::make('brand')
                ->label('Marca (Nombre)')
                ->relationship(resolveUsing: 'name'),

            ImportColumn::make('quantity')
                ->label('Stock')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('low_stock_threshold')
                ->label('Umbral Stock Bajo')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('weight')
                ->label('Peso')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('warranty_months')
                ->label('Garantia (meses)')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('is_active')
                ->label('Activo (1/0)')
                ->boolean(),

            ImportColumn::make('is_featured')
                ->label('Destacado (1/0)')
                ->boolean(),

            ImportColumn::make('is_new')
                ->label('Nuevo (1/0)')
                ->boolean(),
        ];
    }

    public function resolveRecord(): ?Product
    {
        if ($this->options['updateExisting'] ?? false) {
            return Product::firstOrNew([
                'sku' => $this->data['sku'],
            ]);
        }

        return new Product([
            'quantity' => 0,
            'is_active' => true,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importacion de productos completada. '.number_format($import->successful_rows).' '.str('fila')->plural($import->successful_rows).' importada(s).';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('fila')->plural($failedRowsCount).' fallo.';
        }

        return $body;
    }
}
