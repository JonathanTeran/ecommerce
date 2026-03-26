<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('account.orders') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-6">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                {{ __('Volver a Mis Pedidos') }}
            </a>

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white">{{ __('Pedido') }} {{ $order->order_number }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @switch($order->status->value)
                        @case('pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 @break
                        @case('confirmed') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 @break
                        @case('processing') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 @break
                        @case('shipped') bg-cyan-100 text-cyan-800 dark:bg-cyan-900/20 dark:text-cyan-400 @break
                        @case('delivered') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 @break
                        @case('cancelled') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @break
                        @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                    @endswitch
                ">
                    {{ $order->status->getLabel() }}
                </span>
            </div>

            {{-- Tracking --}}
            @if($order->tracking_number)
                <div class="bg-cyan-50 dark:bg-cyan-900/10 border border-cyan-200 dark:border-cyan-800 rounded-xl p-4 mb-6">
                    <p class="text-sm font-medium text-cyan-800 dark:text-cyan-300">
                        {{ __('Numero de Rastreo') }}: <span class="font-bold">{{ $order->tracking_number }}</span>
                        @if($order->tracking_url)
                            — <a href="{{ $order->tracking_url }}" target="_blank" class="underline">{{ __('Rastrear') }}</a>
                        @endif
                    </p>
                </div>
            @endif

            {{-- Items --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-100 dark:border-zinc-800">
                    <h2 class="font-bold text-gray-900 dark:text-white">{{ __('Productos') }}</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-zinc-800">
                    @foreach($order->items as $item)
                        <div class="p-6 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ $item->sku }} &middot; Cant: {{ $item->quantity }}</p>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white">${{ number_format($item->subtotal, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Totals --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 mb-6">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ __('Subtotal') }}</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>{{ __('Descuento') }} {{ $order->coupon_code ? "({$order->coupon_code})" : '' }}</span>
                            <span>-${{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ __('IVA') }}</span>
                        <span>${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @if($order->shipping_amount > 0)
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ __('Envio') }}</span>
                            <span>${{ number_format($order->shipping_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-zinc-700">
                        <span>{{ __('Total') }}</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($order->shipping_address)
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-3">{{ __('Direccion de Envio') }}</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <p>{{ $order->shipping_address['name'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['address'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['phone'] ?? '' }}</p>
                        </div>
                    </div>
                @endif
                @if($order->billing_address)
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-3">{{ __('Direccion de Facturacion') }}</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <p>{{ $order->billing_address['name'] ?? '' }}</p>
                            <p>{{ $order->billing_address['address'] ?? '' }}</p>
                            <p>{{ $order->billing_address['city'] ?? '' }}, {{ $order->billing_address['state'] ?? '' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
