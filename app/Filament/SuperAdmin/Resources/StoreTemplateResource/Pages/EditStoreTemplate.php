<?php

namespace App\Filament\SuperAdmin\Resources\StoreTemplateResource\Pages;

use App\Filament\SuperAdmin\Resources\StoreTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoreTemplate extends EditRecord
{
    protected static string $resource = StoreTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
