<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Success Header --}}
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="font-heading text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3">
                    {{ __('Cotización Enviada') }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    {{ __('Tu solicitud de cotización ha sido recibida. Nuestro equipo la revisará pronto.') }}
                </p>
            </div>

            {{-- Quotation Summary Card --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                <div class="bg-primary-50 dark:bg-primary-900/10 px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Número de Cotización') }}</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $quotation->quotation_number }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Válida hasta') }}</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $quotation->valid_until->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('Productos') }}</h2>
                    <div class="space-y-4">
                        @foreach ($quotation->items as $item)
                            <div class="flex gap-4 pb-4 border-b border-gray-100 dark:border-zinc-800 last:border-0 last:pb-0">
                                <div class="flex flex-1 flex-col justify-center">
                                    <div class="flex justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('Cantidad') }}: {{ $item->quantity }} x ${{ number_format($item->price, 2) }}
                                            </p>
                                        </div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($item->subtotal, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Totals --}}
                <div class="bg-gray-50 dark:bg-zinc-800/50 px-6 py-5 border-t border-gray-100 dark:border-zinc-800">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                            <span class="text-gray-900 dark:text-white">${{ number_format($quotation->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('IVA') }}</span>
                            <span class="text-gray-900 dark:text-white">${{ number_format($quotation->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-zinc-700">
                            <span class="text-base font-bold text-gray-900 dark:text-white">{{ __('Total') }}</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($quotation->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="px-6 py-5 border-t border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-900/20">
                                <svg class="h-5 w-5 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ __('Estado') }}: {{ $quotation->status->getLabel() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Recibirás una notificación cuando sea revisada.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer Notes & Shipping Address --}}
            @if($quotation->customer_notes || $quotation->shipping_address)
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden mt-6">
                    <div class="p-6 space-y-5">
                        @if($quotation->customer_notes)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">{{ __('Notas') }}</h3>
                                <p class="text-gray-700 dark:text-gray-300">{{ $quotation->customer_notes }}</p>
                            </div>
                        @endif
                        @if($quotation->shipping_address)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">{{ __('Dirección de Envío') }}</h3>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ $quotation->shipping_address['name'] ?? '' }}<br>
                                    {{ $quotation->shipping_address['address'] ?? '' }}<br>
                                    {{ $quotation->shipping_address['city'] ?? '' }}{{ !empty($quotation->shipping_address['state']) ? ', ' . $quotation->shipping_address['state'] : '' }} {{ $quotation->shipping_address['zip'] ?? '' }}
                                    @if(!empty($quotation->shipping_address['phone']))
                                        <br>{{ __('Tel') }}: {{ $quotation->shipping_address['phone'] }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('quotations.index') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition shadow-lg shadow-primary-500/20">
                    {{ __('Ver Mis Cotizaciones') }}
                </a>
                <a href="{{ url('/shop') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-700 rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                    {{ __('Seguir Comprando') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
