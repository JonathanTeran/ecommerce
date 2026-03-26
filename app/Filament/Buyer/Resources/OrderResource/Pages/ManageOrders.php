<?php

namespace App\Filament\Buyer\Resources\OrderResource\Pages;

use App\Filament\Buyer\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
