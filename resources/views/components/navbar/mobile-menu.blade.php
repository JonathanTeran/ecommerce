<!-- Mobile Menu -->
<div id="mobile-menu" x-show="mobileMenuOpen" x-transition x-cloak
    class="md:hidden bg-white dark:bg-dark-surface border-t border-slate-100 dark:border-slate-800">
    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        @if($defaultMenu)
            <a href="{{ route('home') }}" wire:navigate
                class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Home') }}</a>
            <a href="{{ route('shop.index') }}" wire:navigate
                class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Shop') }}</a>
            @if($brandsEnabled)
            <a href="{{ route('brands.index') }}" wire:navigate
                class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Brands') }}</a>
            @endif
            <a href="{{ route('about') }}" wire:navigate
                class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('About') }}</a>
        @else
            @foreach($menuItems as $item)
                <a href="{{ $item['url'] }}" wire:navigate
                    @if($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ $item['label'] }}</a>
            @endforeach
        @endif

        <div class="border-t border-slate-200 dark:border-slate-700 mt-4 pt-4 pb-2">
            @auth
                <div class="flex items-center px-3 mb-3">
                    <div class="ml-3">
                        <div class="text-base font-medium leading-none text-slate-800 dark:text-white">
                            {{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium leading-none text-slate-500 dark:text-slate-400">
                            {{ auth()->user()->email }}</div>
                    </div>
                </div>
                @if (auth()->user()->is_admin)
                    <a href="{{ url('/admin') }}" wire:navigate
                        class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('account.orders') }}" wire:navigate
                        class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('My Orders') }}</a>
                    <a href="{{ route('account.profile') }}" wire:navigate
                        class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Profile') }}</a>
                    <a href="{{ route('account.wishlist') }}" wire:navigate
                        class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Wishlist') }}</a>
                @endif
                <form method="POST" action="{{ route('account.logout') }}" class="mt-1">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Logout') }}</button>
                </form>
            @else
                <button @click="$dispatch('open-auth-modal', { mode: 'login' }); mobileMenuOpen = false"
                    class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Login') }}</button>
                <button @click="$dispatch('open-auth-modal', { mode: 'register' }); mobileMenuOpen = false"
                    class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-slate-700 dark:text-slate-200 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Register') }}</button>
            @endauth
        </div>
    </div>
</div>
