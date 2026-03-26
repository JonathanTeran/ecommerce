<?php

namespace App\Filament\SuperAdmin\Resources\GlobalIntegrationResource\Pages;

use App\Filament\SuperAdmin\Resources\GlobalIntegrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlobalIntegrations extends ListRecords
{
    protected static string $resource = GlobalIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
