<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Concerns\HandlesTranslatableFields;
use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    use HandlesTranslatableFields;

    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
