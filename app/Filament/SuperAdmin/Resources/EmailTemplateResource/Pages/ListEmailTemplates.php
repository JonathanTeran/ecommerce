<?php

namespace App\Filament\SuperAdmin\Resources\EmailTemplateResource\Pages;

use App\Filament\SuperAdmin\Resources\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
