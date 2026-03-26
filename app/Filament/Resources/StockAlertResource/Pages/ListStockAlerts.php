<?php

namespace App\Filament\Resources\StockAlertResource\Pages;

use App\Filament\Resources\StockAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockAlerts extends ListRecords
{
    protected static string $resource = StockAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
