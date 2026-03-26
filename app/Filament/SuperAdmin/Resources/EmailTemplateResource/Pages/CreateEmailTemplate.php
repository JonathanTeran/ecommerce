<?php

namespace App\Filament\SuperAdmin\Resources\EmailTemplateResource\Pages;

use App\Filament\SuperAdmin\Resources\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
