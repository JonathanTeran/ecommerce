<?php

namespace App\Filament\Resources\StockAlertResource\Pages;

use App\Filament\Resources\StockAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockAlert extends EditRecord
{
    protected static string $resource = StockAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
