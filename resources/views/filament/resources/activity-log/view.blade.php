<div class="space-y-4 p-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Evento:</span>
            <p class="mt-1">{{ $record->event ?? '-' }}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha:</span>
            <p class="mt-1">{{ $record->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo:</span>
            <p class="mt-1">{{ $record->subject_type ? class_basename($record->subject_type) . ' #' . $record->subject_id : '-' }}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuario:</span>
            <p class="mt-1">{{ $record->causer?->name ?? 'Sistema' }}</p>
        </div>
    </div>

    @if($record->properties && $record->properties->count() > 0)
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Cambios:</span>
            <div class="mt-2 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                @if($record->properties->has('old'))
                    <div class="mb-2">
                        <span class="text-xs font-semibold text-red-600 dark:text-red-400">Anterior:</span>
                        <pre class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ json_encode($record->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
                @if($record->properties->has('attributes'))
                    <div>
                        <span class="text-xs font-semibold text-green-600 dark:text-green-400">Nuevo:</span>
                        <pre class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ json_encode($record->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
