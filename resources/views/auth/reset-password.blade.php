<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen flex items-center justify-center py-16 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
                <div class="text-center mb-8">
                    @if($tenantSettings?->site_logo)
                        <img src="{{ url('storage/' . $tenantSettings->site_logo) }}" alt="{{ $tenantSettings->site_name ?? config('app.name') }}"
                            class="h-16 w-auto object-contain mx-auto mb-4">
                    @else
                        <span class="font-heading font-bold text-2xl text-gray-900 dark:text-white block mb-4">
                            {{ $tenantSettings?->site_name ?? config('app.name') }}
                        </span>
                    @endif
                    <h1 class="text-2xl font-bold font-heading text-gray-900 dark:text-white">{{ __('Reset Password') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('Enter your new password below.') }}</p>
                </div>

                <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}" required
                            class="w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                        @error('email')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('New Password') }}</label>
                        <input type="password" name="password" required
                            class="w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                        @error('password')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full rounded-xl border-0 py-2.5 text-gray-900 dark:text-white dark:bg-zinc-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-700 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm">
                    </div>

                    <button type="submit"
                        class="flex w-full justify-center rounded-xl bg-primary-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 transition-all">
                        {{ __('Reset Password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
