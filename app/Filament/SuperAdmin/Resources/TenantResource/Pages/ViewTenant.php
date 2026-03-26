<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Pages;

use App\Filament\SuperAdmin\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\EditAction::make(),
        ];

        $impersonationTarget = $this->getRecord()->users()->first();

        if ($impersonationTarget) {
            $actions[] = \STS\FilamentImpersonate\Pages\Actions\Impersonate::make('impersonate')
                ->record($impersonationTarget);
        }

        return $actions;
    }
}
