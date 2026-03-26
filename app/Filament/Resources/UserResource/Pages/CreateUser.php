<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()->isSuperAdmin()) {
            $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

            if ($tenant) {
                $data['tenant_id'] = $tenant->id;
            }
        }

        return $data;
    }
}
