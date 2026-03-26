<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $locale = app()->getLocale();
        // List of fields that are translatable (from Product model)
        $translatableFields = ['name', 'description', 'short_description', 'specifications', 'meta_title', 'meta_description'];

        foreach ($translatableFields as $field) {
            // If the field is an array (JSON), extract the current locale string
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][$locale] ?? array_values($data[$field])[0] ?? '';
            }
        }

        $customAttributes = [];
        $attributeValues = \App\Models\AttributeValue::where('product_id', $this->record->id)->get();

        foreach ($attributeValues as $attributeValue) {
            $customAttributes[$attributeValue->attribute_id] = $attributeValue->value;
        }

        $data['custom_attributes'] = $customAttributes;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        $customAttributes = $this->data['custom_attributes'] ?? [];

        foreach ($customAttributes as $attributeId => $value) {
            if ($value !== null && $value !== '') {
                \App\Models\AttributeValue::updateOrCreate(
                    ['attribute_id' => $attributeId, 'product_id' => $this->record->id],
                    ['value' => (string) $value]
                );
            } else {
                \App\Models\AttributeValue::where('attribute_id', $attributeId)
                    ->where('product_id', $this->record->id)
                    ->delete();
            }
        }
    }
}
