<div class="space-y-3">
    @forelse($cart->items as $item)
        <div class="flex items-center gap-4 rounded-lg border p-3 dark:border-gray-700">
            @if($item->product?->thumbnail_url)
                <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" class="h-12 w-12 rounded object-cover">
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded bg-gray-100 dark:bg-gray-800">
                    <x-heroicon-o-photo class="h-6 w-6 text-gray-400" />
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <div class="truncate font-medium text-sm">{{ $item->product?->name ?? 'Producto eliminado' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    SKU: {{ $item->product?->sku ?? '—' }}
                    @if($item->variant)
                        | Variante: {{ $item->variant->name ?? $item->variant_id }}
                    @endif
                </div>
            </div>
            <div class="text-right">
                <div class="font-medium text-sm">${{ number_format($item->price, 2) }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">x{{ $item->quantity }}</div>
            </div>
            <div class="font-bold text-sm text-primary-600">
                ${{ number_format($item->price * $item->quantity, 2) }}
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">El carrito está vacío.</p>
    @endforelse

    @if($cart->items->isNotEmpty())
        <div class="flex justify-end border-t pt-3 dark:border-gray-700">
            <div class="text-lg font-bold">
                Total: ${{ number_format($cart->items->sum(fn ($item) => $item->price * $item->quantity), 2) }}
            </div>
        </div>
    @endif
</div>
