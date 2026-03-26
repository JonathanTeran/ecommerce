<?php

namespace App\Filament\SuperAdmin\Resources\SuperAdminUserResource\Pages;

use App\Filament\SuperAdmin\Resources\SuperAdminUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSuperAdminUser extends CreateRecord
{
    protected static string $resource = SuperAdminUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $this->record->assignRole('super_admin');
    }
}
