<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $customAttributes = $this->data['custom_attributes'] ?? [];

        foreach ($customAttributes as $attributeId => $value) {
            if ($value !== null && $value !== '') {
                \App\Models\AttributeValue::create([
                    'attribute_id' => $attributeId,
                    'product_id' => $this->record->id,
                    'value' => (string) $value,
                ]);
            }
        }
    }
}
