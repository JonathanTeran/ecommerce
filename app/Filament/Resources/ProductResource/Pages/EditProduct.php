<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\HandlesTranslatableFields;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesTranslatableFields;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Resolve translatable fields via trait
        $data = $this->handleTranslatableFieldsBeforeFill($data);

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
