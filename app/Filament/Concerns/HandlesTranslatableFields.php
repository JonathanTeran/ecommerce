<?php

namespace App\Filament\Concerns;

trait HandlesTranslatableFields
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->handleTranslatableFieldsBeforeFill($data);
    }

    protected function handleTranslatableFieldsBeforeFill(array $data): array
    {
        $locale = app()->getLocale();
        $model = $this->getRecord();

        if (! property_exists($model, 'translatable')) {
            return $data;
        }

        foreach ($model->translatable as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][$locale]
                    ?? $data[$field][config('app.fallback_locale')]
                    ?? $data[$field]['en']
                    ?? (is_string(reset($data[$field])) ? reset($data[$field]) : '');
            }
        }

        return $data;
    }
}
