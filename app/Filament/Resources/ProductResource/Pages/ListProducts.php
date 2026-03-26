<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_template')
                ->label('Descargar Plantilla (Excel)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $writer = new \OpenSpout\Writer\XLSX\Writer;
                        $writer->openToFile('php://output');

                        $headers = ['name', 'sku', 'price', 'description', 'category', 'brand', 'quantity', 'is_active'];
                        $row = \OpenSpout\Common\Entity\Row::fromValues($headers);
                        $writer->addRow($row);

                        $writer->close();
                    }, 'plantilla_productos.xlsx');
                }),

            Actions\Action::make('download_sku_list')
                ->label('Descargar Lista SKU (Excel)')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $writer = new \OpenSpout\Writer\XLSX\Writer;
                        $writer->openToFile('php://output');

                        $headers = ['Nombre del Producto', 'SKU'];
                        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues($headers));

                        \App\Models\Product::query()
                            ->select('name', 'sku')
                            ->chunk(1000, function ($products) use ($writer) {
                                foreach ($products as $product) {
                                    $row = \OpenSpout\Common\Entity\Row::fromValues([
                                        $product->name,
                                        $product->sku,
                                    ]);
                                    $writer->addRow($row);
                                }
                            });

                        $writer->close();
                    }, 'lista_skus.xlsx');
                }),

            Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\ProductImporter::class)
                ->label('Importar Productos')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success'),

            Actions\ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label('Exportar Productos')
                ->icon('heroicon-o-arrow-down-tray'),

            Actions\Action::make('bulk_upload_images')
                ->label('Subir Imágenes Masivas')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('images')
                        ->label('Seleccionar Imágenes')
                        ->helperText('Sube múltiples imágenes. El nombre del archivo debe coincidir con el SKU (ej: IPHONE13.jpg)')
                        ->multiple()
                        ->preserveFilenames()
                        ->storeFiles(false) // Handle storage manually
                        ->required(),
                ])
                ->action(function (array $data, \Filament\Notifications\Notification $notification) {
                    $files = $data['images'];
                    $count = 0;
                    $failed = 0;

                    foreach ($files as $file) {
                        try {
                            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                            // Clean filename if needed to match SKU format

                            $product = \App\Models\Product::where('sku', $filename)->first();

                            if ($product) {
                                $product->addMedia($file)
                                    ->toMediaCollection('images');
                                $count++;
                            } else {
                                $failed++;
                            }
                        } catch (\Exception $e) {
                            $failed++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Carga de imágenes completada')
                        ->body("Se asignaron $count imágenes exitosamente. $failed no encontraron SKU coincidente.")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
