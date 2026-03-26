<?php

namespace App\Filament\SuperAdmin\Resources\GlobalIntegrationResource\Pages;

use App\Filament\SuperAdmin\Resources\GlobalIntegrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalIntegration extends EditRecord
{
    protected static string $resource = GlobalIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
