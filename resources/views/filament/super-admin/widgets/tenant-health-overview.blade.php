<x-filament-widgets::widget>
    <x-filament::section heading="Salud de Tenants" icon="heroicon-o-heart">
        <div class="space-y-3">
            @forelse($this->getTenantHealthData() as $tenant)
                <div class="flex items-center gap-4">
                    <div class="w-40 shrink-0 truncate text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $tenant['name'] }}
                    </div>
                    <div class="flex-1">
                        <div class="h-4 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-full rounded-full transition-all duration-500 @if($tenant['color'] === 'success') bg-green-500 @elseif($tenant['color'] === 'warning') bg-yellow-500 @else bg-red-500 @endif"
                                style="width: {{ $tenant['score'] }}%"
                            ></div>
                        </div>
                    </div>
                    <div class="w-12 shrink-0 text-right text-sm font-semibold @if($tenant['color'] === 'success') text-green-600 dark:text-green-400 @elseif($tenant['color'] === 'warning') text-yellow-600 dark:text-yellow-400 @else text-red-600 dark:text-red-400 @endif">
                        {{ $tenant['score'] }}%
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay tenants activos.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
