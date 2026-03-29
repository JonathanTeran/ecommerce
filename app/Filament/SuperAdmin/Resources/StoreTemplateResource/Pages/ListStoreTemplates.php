<?php

namespace App\Filament\SuperAdmin\Resources\StoreTemplateResource\Pages;

use App\Filament\SuperAdmin\Resources\StoreTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoreTemplates extends ListRecords
{
    protected static string $resource = StoreTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
