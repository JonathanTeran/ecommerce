<div>
    @php
        $imageUrl = $getState();
    @endphp

    <div
        class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="Product Image" class="h-full w-full object-cover object-center">
        @else
            <div class="h-full w-full flex items-center justify-center text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
        @endif
    </div>
</div>
