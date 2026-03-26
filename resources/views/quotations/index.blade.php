<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ __('Mis Cotizaciones') }}</h1>

            @if($quotations->isEmpty())
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-zinc-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('No tienes cotizaciones aún.') }}</p>
                    <a href="{{ url('/shop') }}" class="inline-flex items-center mt-4 px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition">
                        {{ __('Ir a la Tienda') }}
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($quotations as $quotation)
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $quotation->quotation_number }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($quotation->status->value)
                                                @case('pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 @break
                                                @case('approved') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 @break
                                                @case('rejected') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @break
                                                @case('converted') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 @break
                                                @case('expired') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @break
                                                @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endswitch
                                        ">
                                            {{ $quotation->status->getLabel() }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ __('Fecha') }}: {{ $quotation->created_at->format('d/m/Y') }}</span>
                                        <span>{{ __('Total') }}: ${{ number_format($quotation->total, 2) }}</span>
                                        @if($quotation->valid_until)
                                            <span class="{{ $quotation->is_expired ? 'text-red-600 dark:text-red-400 font-medium' : ($quotation->valid_until->diffInDays(today()) <= 3 && !$quotation->is_expired ? 'text-amber-600 dark:text-amber-400 font-medium' : '') }}">
                                                {{ __('Válida hasta') }}: {{ $quotation->valid_until->format('d/m/Y') }}
                                                @if($quotation->is_expired)
                                                    ({{ __('Expirada') }})
                                                @elseif($quotation->valid_until->diffInDays(today()) <= 3)
                                                    ({{ __('Expira pronto') }})
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('quotations.show', $quotation) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-zinc-700 transition">
                                        {{ __('Ver Detalle') }}
                                    </a>
                                    @if($quotation->status->value !== 'draft')
                                        <a href="{{ route('quotations.pdf', $quotation) }}"
                                            class="inline-flex items-center px-4 py-2 bg-primary-100 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-lg text-sm font-medium hover:bg-primary-200 dark:hover:bg-primary-900/30 transition">
                                            PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $quotations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
