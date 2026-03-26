@props(['section', 'data' => []])
@php
    $config = $section->config;
    $categories = $data['categories'] ?? collect();
    $configCategories = $config['categories'] ?? [];
    $style = $config['style'] ?? [];
    $customCss = \App\Support\SectionStyleHelper::toInlineCss($style);
    $hasCustomStyle = $customCss !== '';
@endphp

<div class="{{ $hasCustomStyle ? '' : 'bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800 py-8' }}"
    @if($hasCustomStyle) style="{!! $customCss !!}" @endif>
    <div class="container mx-auto px-4">
        <div class="flex gap-8 justify-center flex-wrap pb-4">
            @if(count($configCategories) > 0)
                @foreach($configCategories as $cat)
                    @php
                        $category = $categories->firstWhere('id', $cat['category_id'] ?? null);
                    @endphp
                    @if($category)
                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                            class="flex flex-col items-center gap-3 min-w-[100px] snap-center group cursor-pointer">
                            <div class="w-20 h-20 rounded-full {{ $cat['color_class'] ?? 'bg-blue-100 text-blue-600' }} flex items-center justify-center text-3xl shadow-sm group-hover:scale-110 transition duration-300 group-hover:shadow-md">
                                @if($category->getFirstMediaUrl('image'))
                                    <img src="{{ $category->getFirstMediaUrl('image') }}" alt="{{ $category->name }}" class="w-10 h-10 object-contain">
                                @else
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                @endif
                            </div>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-primary-600 transition">{{ $category->name }}</span>
                        </a>
                    @endif
                @endforeach
            @else
                @foreach($categories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                        class="flex flex-col items-center gap-3 min-w-[100px] snap-center group cursor-pointer">
                        <div class="w-20 h-20 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-3xl shadow-sm group-hover:scale-110 transition duration-300 group-hover:shadow-md">
                            @if($category->getFirstMediaUrl('image'))
                                <img src="{{ $category->getFirstMediaUrl('image') }}" alt="{{ $category->name }}" class="w-10 h-10 object-contain">
                            @else
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            @endif
                        </div>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-primary-600 transition">{{ $category->name }}</span>
                        @php $catCount = $category->all_products_count ?? $category->products_count ?? 0; @endphp
                        @if($catCount > 0)
                            <span class="text-xs text-gray-400 dark:text-gray-500 -mt-1">{{ $catCount }} {{ __('productos') }}</span>
                        @endif
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</div>
