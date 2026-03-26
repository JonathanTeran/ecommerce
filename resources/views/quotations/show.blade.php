<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <a href="{{ route('quotations.index') }}" class="text-sm text-primary-600 hover:text-primary-700 mb-2 inline-block">&larr; {{ __('Volver a Mis Cotizaciones') }}</a>
                    <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white">{{ $quotation->quotation_number }}</h1>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
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
                    <a href="{{ route('quotations.pdf', $quotation) }}"
                        class="inline-flex items-center px-4 py-1.5 bg-primary-600 text-white rounded-full text-sm font-medium hover:bg-primary-700 transition">
                        {{ __('Descargar PDF') }}
                    </a>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Rejection Reason --}}
                @if($quotation->status->value === 'rejected' && $quotation->rejection_reason)
                    <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-2xl p-6">
                        <h3 class="font-bold text-red-800 dark:text-red-400 mb-2">{{ __('Razón de Rechazo') }}</h3>
                        <p class="text-red-700 dark:text-red-300">{{ $quotation->rejection_reason }}</p>
                    </div>
                @endif

                {{-- Converted Order --}}
                @if($quotation->convertedOrder)
                    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-2xl p-6">
                        <h3 class="font-bold text-blue-800 dark:text-blue-400 mb-2">{{ __('Pedido Generado') }}</h3>
                        <p class="text-blue-700 dark:text-blue-300">
                            {{ __('Esta cotización fue convertida al pedido') }} <strong>{{ $quotation->convertedOrder->order_number }}</strong>
                        </p>
                    </div>
                @endif

                {{-- Items --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Productos') }}</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach ($quotation->items as $item)
                            <div class="flex gap-4 pb-4 border-b border-gray-100 dark:border-zinc-800 last:border-0 last:pb-0">
                                <div class="flex flex-1 flex-col justify-center">
                                    <div class="flex justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                SKU: {{ $item->sku }} &middot; {{ $item->quantity }} x ${{ number_format($item->price, 2) }}
                                            </p>
                                        </div>
                                        <p class="font-medium text-gray-900 dark:text-white">${{ number_format($item->subtotal, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div class="bg-gray-50 dark:bg-zinc-800/50 px-6 py-5 border-t border-gray-100 dark:border-zinc-800">
                        <div class="space-y-2 max-w-xs ml-auto">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                                <span class="text-gray-900 dark:text-white">${{ number_format($quotation->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('IVA') }}</span>
                                <span class="text-gray-900 dark:text-white">${{ number_format($quotation->tax_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-zinc-700">
                                <span class="font-bold text-gray-900 dark:text-white">{{ __('Total') }}</span>
                                <span class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($quotation->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Customer Info --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4">{{ __('Datos del Solicitante') }}</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-gray-500 dark:text-gray-400">{{ __('Nombre') }}:</span> <span class="text-gray-900 dark:text-white">{{ $quotation->customer_name }}</span></p>
                            <p><span class="text-gray-500 dark:text-gray-400">{{ __('Email') }}:</span> <span class="text-gray-900 dark:text-white">{{ $quotation->customer_email }}</span></p>
                            @if($quotation->customer_phone)
                                <p><span class="text-gray-500 dark:text-gray-400">{{ __('Teléfono') }}:</span> <span class="text-gray-900 dark:text-white">{{ $quotation->customer_phone }}</span></p>
                            @endif
                            @if($quotation->customer_company)
                                <p><span class="text-gray-500 dark:text-gray-400">{{ __('Empresa') }}:</span> <span class="text-gray-900 dark:text-white">{{ $quotation->customer_company }}</span></p>
                            @endif
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4">{{ __('Fechas') }}</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-gray-500 dark:text-gray-400">{{ __('Enviada') }}:</span> <span class="text-gray-900 dark:text-white">{{ $quotation->placed_at?->format('d/m/Y H:i') ?? '-' }}</span></p>
                            <p><span class="text-gray-500 dark:text-gray-400">{{ __('Válida hasta') }}:</span> <span class="{{ $quotation->is_expired ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $quotation->valid_until?->format('d/m/Y') ?? '-' }}</span></p>
                            @if($quotation->approved_at)
                                <p><span class="text-gray-500 dark:text-gray-400">{{ __('Aprobada') }}:</span> <span class="text-gray-900 dark:text-white">{{ $quotation->approved_at->format('d/m/Y H:i') }}</span></p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                @if($quotation->customer_notes || $quotation->admin_notes)
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4">{{ __('Notas') }}</h3>
                        @if($quotation->customer_notes)
                            <div class="mb-4">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">{{ __('Tus notas') }}</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ $quotation->customer_notes }}</p>
                            </div>
                        @endif
                        @if($quotation->admin_notes)
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">{{ __('Respuesta del equipo') }}</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ $quotation->admin_notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
