<?php

namespace App\Filament\SuperAdmin\Resources\TenantRegistrationResource\Pages;

use App\Filament\SuperAdmin\Resources\TenantRegistrationResource;
use Filament\Resources\Pages\ListRecords;

class ListTenantRegistrations extends ListRecords
{
    protected static string $resource = TenantRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
