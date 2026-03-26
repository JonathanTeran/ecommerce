<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Success Header --}}
            <div class="text-center mb-10">
                <div
                    class="w-20 h-20 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="font-heading text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3">
                    {{ __('Pedido Realizado con Éxito') }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    {{ __('Gracias por tu compra. Hemos recibido tu pedido.') }}
                </p>
            </div>

            {{-- Order Summary Card --}}
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                {{-- Order Header --}}
                <div
                    class="bg-primary-50 dark:bg-primary-900/10 px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Número de Pedido') }}</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Fecha') }}</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ ($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('Productos') }}</h2>
                    <div class="space-y-4">
                        @foreach ($order->items as $item)
                            <div
                                class="flex gap-4 pb-4 border-b border-gray-100 dark:border-zinc-800 last:border-0 last:pb-0">
                                <div
                                    class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
                                    @php
                                        // Use thumbnail_url accessor which returns full URL or null
                                        $imageUrl = $item->product?->thumbnail_url;
                                    @endphp

                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item->name }}"
                                            class="h-full w-full object-cover object-center">
                                    @else
                                        <div
                                            class="h-full w-full flex items-center justify-center text-gray-400 bg-gray-50 dark:bg-zinc-800">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col justify-center">
                                    <div class="flex justify-between">
                                        <div>
                                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $item->name }}
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('Cantidad') }}: {{ $item->quantity }} x
                                                ${{ number_format($item->price, 2) }}
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

                {{-- Payment & Totals --}}
                <div class="bg-gray-50 dark:bg-zinc-800/50 px-6 py-5 border-t border-gray-100 dark:border-zinc-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Payment Info --}}
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">{{ __('Método de Pago') }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ $order->payment_method?->getLabel() ?? 'N/A' }}</p>
                            <div class="mt-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($order->payment_status->value === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                    @elseif($order->payment_status->value === 'completed')
                                        bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                    @else
                                        bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif
                                ">
                                    {{ __('Estado de Pago') }}: {{ $order->payment_status->getLabel() }}
                                </span>
                            </div>
                        </div>

                        {{-- Totals --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                                <span
                                    class="text-gray-900 dark:text-white">${{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            @if ($order->surcharge_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Recargo') }}</span>
                                    <span
                                        class="text-gray-900 dark:text-white">${{ number_format($order->surcharge_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Envío') }}</span>
                                @if(($order->shipping_amount ?? 0) > 0)
                                    <span class="font-medium text-gray-900 dark:text-white">{{ \App\Models\GeneralSetting::getCurrencySymbol() }}{{ number_format($order->shipping_amount, 2) }}</span>
                                @else
                                    <span class="text-green-600 font-medium">{{ __('Gratis') }}</span>
                                @endif
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-zinc-700">
                                <span
                                    class="text-base font-bold text-gray-900 dark:text-white">{{ __('Total') }}</span>
                                <span
                                    class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Status --}}
                <div class="px-6 py-5 border-t border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/20">
                                <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ __('Estado del Pedido') }}:
                                {{ $order->status->getLabel() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Recibirás actualizaciones por correo electrónico.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/buyer') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition shadow-lg shadow-primary-500/20">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    {{ __('Ver Mis Pedidos') }}
                </a>
                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-700 rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    {{ __('Seguir Comprando') }}
                </a>
            </div>
        </div>
    </div>
    {{-- Ecommerce Tracking: purchase --}}
    @php
        $trackingItems = $order->items->map(function ($item) {
            return [
                'item_id' => $item->product_id,
                'item_name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
            ];
        })->values()->toArray();
    @endphp
    @push('head')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.trackEcommerce === 'function') {
                window.trackEcommerce('purchase', {
                    transaction_id: @json($order->order_number),
                    value: {{ $order->total }},
                    tax: {{ $order->tax_amount ?? 0 }},
                    shipping: {{ $order->shipping_amount ?? 0 }},
                    items: @json($trackingItems)
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>
