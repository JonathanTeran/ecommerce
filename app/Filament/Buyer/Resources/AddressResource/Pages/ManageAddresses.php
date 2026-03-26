<?php

namespace App\Filament\Buyer\Resources\AddressResource\Pages;

use App\Filament\Buyer\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAddresses extends ManageRecords
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
