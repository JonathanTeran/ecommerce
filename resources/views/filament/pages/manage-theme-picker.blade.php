<x-filament-panels::page>
    @php
        $baseThemes = $this->baseThemes;
        $storeTemplates = $this->storeTemplates;
        $allTemplates = array_merge($baseThemes, $storeTemplates);
        $categories = [
            'all' => 'Todas',
            'base' => 'Estilos Base',
            'fashion' => 'Moda',
            'jewelry' => 'Joyeria',
            'electronics' => 'Electronica',
            'general' => 'General',
        ];
    @endphp

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Plantilla activa actualmente:</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->getCurrentTemplateName() }}</p>
        </div>
        <div class="flex gap-3">
            <x-filament::button color="gray" icon="heroicon-o-eye" wire:click="openPreview" :disabled="!$selectedThemeTemplate && !$selectedStoreTemplateId">
                Previsualizar
            </x-filament::button>
            <x-filament::button icon="heroicon-o-check" wire:click="applyTemplate">
                Aplicar Plantilla
            </x-filament::button>
        </div>
    </div>

    {{-- Category Tabs --}}
    <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 dark:border-gray-700 pb-4" x-data="{ active: @entangle('activeCategory') }">
        @foreach($categories as $key => $label)
            <button
                x-on:click="active = '{{ $key }}'; $wire.set('activeCategory', '{{ $key }}')"
                :class="active === '{{ $key }}'
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" x-data="{ active: @entangle('activeCategory') }">
        @foreach($allTemplates as $template)
            @php
                $isSelected = ($template['type'] === 'theme' && $selectedThemeTemplate === $template['key'])
                    || ($template['type'] === 'store' && $selectedStoreTemplateId === $template['key']);
                $gradientColors = array_slice($template['swatches'] ?? ['#6366f1', '#818cf8'], 0, 2);
            @endphp

            <div
                x-show="active === 'all' || active === '{{ $template['category'] }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                wire:click="{{ $template['type'] === 'theme' ? "selectTheme('{$template['key']}')" : "selectStoreTemplate({$template['key']})" }}"
                class="relative group cursor-pointer rounded-2xl border-2 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col
                    {{ $isSelected
                        ? 'border-primary-500 ring-2 ring-primary-500/20 shadow-lg shadow-primary-500/10'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}"
            >
                {{-- Preview Image / Mockup --}}
                <div class="relative overflow-hidden" style="height: 180px; flex-shrink: 0;">
                    @if($template['preview_image'] ?? null)
                        <img src="{{ $template['preview_image'] }}" alt="{{ $template['name'] }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: top;" class="group-hover:scale-105 transition-transform duration-500">
                    @else
                        @php
                            $allSwatches = $template['swatches'] ?? [];
                            $bg = $allSwatches[0] ?? '#6366f1';
                            $surface = $allSwatches[1] ?? '#818cf8';
                            $textC = $allSwatches[2] ?? '#ffffff';
                            $accent = $allSwatches[3] ?? $allSwatches[0] ?? '#6366f1';
                        @endphp
                        {{-- Browser-style mockup --}}
                        <div class="w-full h-full relative flex flex-col" style="background: #f1f5f9;">
                            {{-- Browser chrome --}}
                            <div class="flex items-center gap-1.5 px-3 py-1.5" style="background: #e2e8f0;">
                                <div class="w-2 h-2 rounded-full" style="background: #ef4444;"></div>
                                <div class="w-2 h-2 rounded-full" style="background: #f59e0b;"></div>
                                <div class="w-2 h-2 rounded-full" style="background: #22c55e;"></div>
                                <div class="flex-1 mx-2 h-3.5 rounded-full" style="background: #ffffff; border: 1px solid #cbd5e1;"></div>
                            </div>
                            {{-- Site content --}}
                            <div class="flex-1 overflow-hidden" style="background: {{ $bg }};">
                                {{-- Navbar --}}
                                <div class="flex items-center justify-between px-3 py-1.5" style="background: {{ $surface }};">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-3 h-3 rounded-sm" style="background: {{ $accent }};"></div>
                                        <div class="w-10 h-1.5 rounded-full" style="background: {{ $textC }};"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-5 h-1 rounded-full" style="background: {{ $textC }}; opacity: 0.5;"></div>
                                        <div class="w-5 h-1 rounded-full" style="background: {{ $textC }}; opacity: 0.5;"></div>
                                        <div class="w-5 h-1 rounded-full" style="background: {{ $textC }}; opacity: 0.5;"></div>
                                    </div>
                                </div>
                                {{-- Hero --}}
                                <div class="px-3 pt-3 pb-2">
                                    <div class="w-20 h-1.5 rounded-full mb-1" style="background: {{ $textC }};"></div>
                                    <div class="w-28 h-2.5 rounded-full mb-1" style="background: {{ $textC }}; opacity: 0.7;"></div>
                                    <div class="w-14 h-4 rounded mt-2 flex items-center justify-center" style="background: {{ $accent }};">
                                        <div class="w-8 h-1 rounded-full" style="background: {{ $bg }};"></div>
                                    </div>
                                </div>
                                {{-- Products --}}
                                <div class="px-3 grid grid-cols-4 gap-1.5">
                                    @for($i = 0; $i < 4; $i++)
                                        <div class="rounded overflow-hidden" style="background: {{ $surface }};">
                                            <div class="h-8" style="background: linear-gradient(135deg, {{ $surface }}, {{ $bg }});"></div>
                                            <div class="p-1">
                                                <div class="w-full h-1 rounded-full mb-0.5" style="background: {{ $textC }}; opacity: 0.4;"></div>
                                                <div class="w-6 h-1 rounded-full" style="background: {{ $accent }};"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Selected Checkmark --}}
                    @if($isSelected)
                        <div class="absolute top-3 right-3 h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    @endif

                    {{-- Category Badge --}}
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $template['category'] === 'base' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}
                            {{ $template['category'] === 'fashion' ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/50 dark:text-pink-300' : '' }}
                            {{ $template['category'] === 'jewelry' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : '' }}
                            {{ $template['category'] === 'electronics' ? 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-300' : '' }}
                            {{ $template['category'] === 'general' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' : '' }}
                        ">
                            {{ $template['category_label'] }}
                        </span>
                        @if($template['is_premium'])
                            <span class="ml-1 inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-amber-400 text-amber-900">
                                <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                PREMIUM
                            </span>
                        @endif
                    </div>

                    {{-- Preview Button on Hover --}}
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <button wire:click.stop="openPreview" class="bg-white text-gray-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-lg hover:bg-gray-100 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Vista Previa
                        </button>
                    </div>
                </div>

                {{-- Card Info --}}
                <div class="p-5">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">{{ $template['name'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">{{ $template['description'] }}</p>

                    {{-- Color Swatches --}}
                    @if(!empty($template['swatches']))
                        <div class="flex items-center gap-1.5 mb-3">
                            @foreach(array_slice($template['swatches'], 0, 5) as $color)
                                <div class="w-5 h-5 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm" style="background-color: {{ $color }};"></div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Features --}}
                    @if(!empty($template['features']))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($template['features'] as $feature)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-400">{{ $feature }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Fonts --}}
                    @if(!empty($template['fonts']))
                        <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                            <span class="font-medium">Fuentes:</span> {{ implode(', ', $template['fonts']) }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Fullscreen Preview Modal (teleported to body to escape Filament stacking context) --}}
    @if($showPreviewModal && $previewUrl)
        <template x-teleport="body">
        <div
            x-data
            x-on:keydown.escape.window="$wire.closePreview()"
            style="position: fixed; inset: 0; z-index: 999999; display: flex; flex-direction: column; background: rgba(0,0,0,0.95);"
        >
            {{-- Toolbar --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 24px; background: #111827;">
                <div class="flex items-center gap-4">
                    <span class="text-white font-semibold text-sm">Vista Previa de Plantilla</span>
                </div>

                {{-- Device Toggle --}}
                <div class="flex items-center gap-2 bg-gray-800 rounded-lg p-1">
                    <button wire:click="setPreviewDevice('desktop')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ $previewDevice === 'desktop' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Escritorio
                    </button>
                    <button wire:click="setPreviewDevice('mobile')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ $previewDevice === 'mobile' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Movil
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <button wire:click="applyTemplate" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all" style="background: #10b981; color: white;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Aplicar esta Plantilla
                    </button>
                    <button wire:click="closePreview" class="text-gray-400 hover:text-white transition-colors p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Iframe Container --}}
            <div style="flex: 1; display: flex; align-items: flex-start; justify-content: center; overflow: hidden; padding: 16px;">
                <div style="height: 100%; width: {{ $previewDevice === 'mobile' ? '375px' : '100%' }}; transition: width 0.5s ease; {{ $previewDevice === 'mobile' ? 'border-radius: 32px; border: 8px solid #374151; box-shadow: 0 0 40px rgba(0,0,0,0.5);' : 'border-radius: 12px; overflow: hidden;' }}">
                    <iframe
                        src="{{ $previewUrl }}"
                        style="width: 100%; height: 100%; border: none; background: white; {{ $previewDevice === 'mobile' ? 'border-radius: 24px;' : 'border-radius: 12px;' }}"
                    ></iframe>
                </div>
            </div>
        </div>
        </template>
    @endif
</x-filament-panels::page>
