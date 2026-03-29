<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $locale = app()->getLocale();

        // For array-type translatable fields (specifically 'specifications'),
        // we must wrap the value in the locale key, otherwise Spatie Translatable
        // interprets the array keys as locales.
        if (isset($data['specifications']) && is_array($data['specifications'])) {
            $data['specifications'] = [$locale => $data['specifications']];
        }

        return $data;
    }

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
