<nav x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)"
    class="fixed w-full z-50 top-0 transition-all duration-300 border-b border-transparent {{ $navStyle === 'solid_dark' ? 'bg-slate-900 dark:bg-dark-surface shadow-sm' : ($navStyle === 'solid_white' ? 'bg-white dark:bg-dark-surface shadow-sm' : '') }}"
    :class="{
        'border-slate-200 dark:border-slate-800': scrolled,
        @if($navStyle === 'transparent_on_scroll')
            'bg-white/80 dark:bg-dark-surface/80 backdrop-blur-md shadow-sm': scrolled,
            'bg-transparent': !scrolled,
        @endif
    }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">

            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    @if ($tenantSettings?->site_logo)
                        <img src="{{ url('storage/' . $tenantSettings->site_logo) }}" alt="Logo"
                            class="h-10 w-auto">
                    @else
                        <span
                            class="font-heading font-bold text-2xl tracking-tight text-slate-900 dark:text-white group-hover:text-primary-500 transition-colors">
                            {{ $tenantSettings?->site_name ?? config('app.name') }}
                        </span>
                    @endif
                </a>
            </div>

            @include('components.navbar.desktop-menu')

            <!-- Desktop Icons -->
            <div class="hidden md:flex items-center gap-4">
                @include('components.navbar.search')
                @include('components.navbar.user-menu')
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-controls="mobile-menu"
                    class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white focus:outline-none">
                    <span class="sr-only">{{ __('Open main menu') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @include('components.navbar.mobile-menu')

    @include('components.navbar.cart-dropdown')
</nav>
