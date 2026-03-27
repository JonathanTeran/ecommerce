<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Concerns\HandlesTranslatableFields;
use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use HandlesTranslatableFields;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
