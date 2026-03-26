<div class="space-y-2 p-2 min-w-[300px]">
    @foreach ($getState() as $item)
        <div class="flex items-center gap-3 border-b border-gray-100 last:border-0 pb-2 last:pb-0 dark:border-zinc-800">
            <div
                class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800">
                @php
                    $imageUrl = $item->product?->thumbnail_url;
                @endphp
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $item->product?->name }}"
                        class="h-full w-full object-cover object-center">
                @else
                    <div class="h-full w-full flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ $item->product?->name }}
                </p>
                <div class="flex justify-between items-center mt-0.5">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Qty: {{ $item->quantity }}
                    </p>
                    <p class="text-xs font-medium text-gray-900 dark:text-white">
                        ${{ number_format($item->subtotal, 2) }}
                    </p>
                </div>
            </div>
        </div>
    @endforeach
</div>
