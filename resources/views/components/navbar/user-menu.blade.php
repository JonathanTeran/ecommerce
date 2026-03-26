{{-- Cart Trigger --}}
<button @click="$store.cart.toggle()"
    class="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors relative group">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
    </svg>
    <span x-show="$store.cart.count > 0" x-transition x-text="$store.cart.count"
        class="absolute -top-1 -right-1 bg-gold-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">0</span>
</button>

@auth
    {{-- Notification Bell --}}
    <div x-data="{
        notifOpen: false,
        unreadCount: 0,
        notifications: [],
        loading: false,
        async fetchUnreadCount() {
            try {
                const res = await fetch('{{ route('notifications.unread-count') }}');
                const data = await res.json();
                this.unreadCount = data.count;
            } catch (e) {}
        },
        async fetchNotifications() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('notifications.index') }}');
                const data = await res.json();
                this.notifications = data.data;
            } catch (e) {}
            this.loading = false;
        },
        async markAsRead(id) {
            try {
                await fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                this.notifications = this.notifications.map(n => {
                    if (n.id === id) { n.read_at = new Date().toISOString(); }
                    return n;
                });
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            } catch (e) {}
        },
        async markAllAsRead() {
            try {
                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                this.notifications = this.notifications.map(n => {
                    n.read_at = new Date().toISOString();
                    return n;
                });
                this.unreadCount = 0;
            } catch (e) {}
        },
        init() {
            this.fetchUnreadCount();
            setInterval(() => this.fetchUnreadCount(), 30000);
        },
        timeAgo(dateStr) {
            const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000);
            if (seconds < 60) return '{{ __('just now') }}';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + ' min';
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + 'h';
            const days = Math.floor(hours / 24);
            return days + 'd';
        }
    }" class="relative">
        <button @click="notifOpen = !notifOpen; if (notifOpen) fetchNotifications()"
            class="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                </path>
            </svg>
            <span x-show="unreadCount > 0" x-transition x-text="unreadCount"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm min-w-4.5 text-center">0</span>
        </button>

        {{-- Notification Dropdown --}}
        <div x-show="notifOpen" @click.away="notifOpen = false" x-transition x-cloak
            class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-xl shadow-2xl z-50 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Notifications') }}</h3>
                <button x-show="unreadCount > 0" @click="markAllAsRead()"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium">{{ __('Mark all as read') }}</button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <div x-show="loading" class="p-4 text-center text-slate-500 text-sm">
                    <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div x-show="!loading && notifications.length === 0" class="p-4 text-center text-slate-500 text-sm">
                    {{ __('No notifications yet.') }}
                </div>

                <template x-for="notification in notifications" :key="notification.id">
                    <div @click="if (!notification.read_at) markAsRead(notification.id)"
                        class="px-4 py-3 border-b border-slate-50 dark:border-zinc-800/50 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer"
                        :class="{ 'bg-primary-50/50 dark:bg-primary-900/10': !notification.read_at }">
                        <p class="text-sm text-slate-700 dark:text-slate-300" x-text="notification.data.message"></p>
                        <p class="text-xs text-slate-400 mt-1" x-text="timeAgo(notification.created_at)"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @if (auth()->user()->is_admin)
        <a href="{{ url('/admin') }}" wire:navigate
            class="text-sm font-medium bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2 rounded-full hover:opacity-90 transition-opacity">
            {{ __('Dashboard') }}
        </a>
    @else
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600">
                {{ auth()->user()->name }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" x-transition
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-xl shadow-xl py-1 z-50">
                <a href="{{ route('account.orders') }}" wire:navigate
                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800">{{ __('My Orders') }}</a>
                <a href="{{ route('account.profile') }}" wire:navigate
                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800">{{ __('Profile') }}</a>
                <a href="{{ route('account.wishlist') }}" wire:navigate
                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800">{{ __('Wishlist') }}</a>
                <form method="POST" action="{{ route('account.logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-50 dark:hover:bg-zinc-800">{{ __('Logout') }}</button>
                </form>
            </div>
        </div>
    @endif
@else
    <button type="button" @click="$dispatch('open-auth-modal', { mode: 'login' })"
        class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-primary-600 transition-colors">{{ __('Login') }}</button>
    <button type="button" @click="$dispatch('open-auth-modal', { mode: 'register' })"
        class="hidden lg:block text-sm font-medium px-4 py-2 bg-slate-900 text-white rounded-full hover:bg-slate-800 transition">{{ __('Register') }}</button>
@endauth
