<!-- Desktop Menu -->
<div class="hidden md:flex space-x-8 items-center">
    @if($navConfig['show_categories_button'] ?? true)
    <a href="{{ route('categories.index') }}" wire:navigate
        class="flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-full font-bold hover:bg-primary-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        {{ __('Products') }}
    </a>
    @endif

    @if($defaultMenu)
        <a href="{{ route('home') }}" wire:navigate
            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Home') }}</a>
        <a href="{{ route('shop.index') }}" wire:navigate
            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Shop') }}</a>
        @if($brandsEnabled)
        <a href="{{ route('brands.index') }}" wire:navigate
            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Brands') }}</a>
        @endif
        <a href="{{ route('about') }}" wire:navigate
            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('About') }}</a>
    @else
        @foreach($menuItems as $item)
            <a href="{{ $item['url'] }}" wire:navigate
                @if($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ $item['label'] }}</a>
        @endforeach
    @endif
</div>
