<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate totals from items
        $data['subtotal'] = 0;
        $data['tax_amount'] = 0;
        $data['total'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->calculateTotals();
    }
}
