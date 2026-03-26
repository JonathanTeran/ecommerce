<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryMovements extends ListRecords
{
    protected static string $resource = InventoryMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_movement')
                ->label('Registrar Movimiento')
                ->icon('heroicon-o-plus')
                ->form([
                    \Filament\Forms\Components\Select::make('product_id')
                        ->label('Producto')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    \Filament\Forms\Components\Select::make('type')
                        ->label('Tipo de Movimiento')
                        ->options([
                            'purchase' => 'Compra (Ingreso)',
                            'return' => 'Devolución (Ingreso)',
                            'adjustment_inc' => 'Ajuste Positivo (+)',
                            'adjustment_dec' => 'Ajuste Negativo (-)',
                        ])
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label('Cantidad')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('unit_cost')
                        ->label('Costo Unitario')
                        ->numeric()
                        ->default(0)
                        ->prefix('$')
                        ->hint('Para ingresos, costo de adquisición. Para egresos, 0 (usa costo actual).'),
                    \Filament\Forms\Components\Select::make('warehouse_location_id')
                        ->label('Ubicación')
                        ->relationship('warehouseLocation', 'name')
                        ->searchable()
                        ->preload(),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notas / Razón')
                        ->placeholder('Razón del movimiento...')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $product = \App\Models\Product::find($data['product_id']);
                    $service = app(\App\Services\InventoryService::class);

                    $quantity = (int) $data['quantity'];
                    $type = $data['type'];
                    $cost = (float) $data['unit_cost'];
                    $notes = $data['notes'] ?? null;
                    $locationId = $data['warehouse_location_id'] ?? null;

                    if (in_array($type, ['purchase', 'return', 'adjustment_inc'])) {
                        $service->addStock($product, $quantity, $cost, $type, notes: $notes, warehouseLocationId: $locationId);
                    } else {
                        $service->removeStock($product, $quantity, $type, notes: $notes, warehouseLocationId: $locationId);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Movimiento registrado correctamente')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('export_csv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $movements = \App\Models\InventoryMovement::with(['product', 'user', 'warehouseLocation'])
                        ->orderByDesc('created_at')
                        ->get();

                    $csv = "Fecha,Producto,Tipo,Cantidad,Costo Unit.,Costo Total,Saldo,Ubicacion,Notas,Usuario\n";

                    foreach ($movements as $m) {
                        $csv .= implode(',', [
                            $m->created_at->format('Y-m-d H:i'),
                            '"' . str_replace('"', '""', $m->product?->name ?? '') . '"',
                            '"' . $m->type_label . '"',
                            $m->quantity,
                            $m->unit_cost,
                            $m->total_cost,
                            $m->balance_quantity,
                            '"' . ($m->warehouseLocation?->name ?? '') . '"',
                            '"' . str_replace('"', '""', $m->notes ?? '') . '"',
                            '"' . ($m->user?->name ?? '') . '"',
                        ]) . "\n";
                    }

                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'kardex_' . now()->format('Y-m-d') . '.csv');
                }),
        ];
    }
}
