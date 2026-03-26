<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white">{{ __('Mis Pedidos') }}</h1>
                <a href="{{ route('account.profile') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">{{ __('Mi Cuenta') }}</a>
            </div>

            @if($orders->isEmpty())
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-zinc-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('No tienes pedidos aun.') }}</p>
                    <a href="{{ url('/shop') }}" class="inline-flex items-center mt-4 px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition">
                        {{ __('Ir a la Tienda') }}
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($order->status->value)
                                                @case('pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 @break
                                                @case('confirmed') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 @break
                                                @case('processing') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 @break
                                                @case('shipped') bg-cyan-100 text-cyan-800 dark:bg-cyan-900/20 dark:text-cyan-400 @break
                                                @case('delivered') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 @break
                                                @case('cancelled') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 @break
                                                @case('refunded') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @break
                                                @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endswitch
                                        ">
                                            {{ $order->status->getLabel() }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $order->created_at->format('d/m/Y') }}</span>
                                        <span>{{ $order->items->count() }} {{ __('productos') }}</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($order->total, 2) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('account.orders.show', $order) }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-zinc-700 transition">
                                    {{ __('Ver Detalle') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
