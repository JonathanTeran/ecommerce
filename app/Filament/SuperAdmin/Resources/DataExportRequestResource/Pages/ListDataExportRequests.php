<?php

namespace App\Filament\SuperAdmin\Resources\DataExportRequestResource\Pages;

use App\Filament\SuperAdmin\Resources\DataExportRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataExportRequests extends ListRecords
{
    protected static string $resource = DataExportRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
