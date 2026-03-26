<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Create Your Store') }} - {{ config('app.name') }}</title>
    {{ \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) }}
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-slate-100">

    {{-- Hero --}}
    <div class="bg-linear-to-br from-indigo-600 to-purple-700 text-white py-16">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <a href="{{ route('home') }}" class="text-white/80 hover:text-white text-sm mb-4 inline-block">&larr; {{ __('Back to home') }}</a>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('Launch Your Online Store') }}</h1>
            <p class="text-xl text-indigo-100 max-w-2xl mx-auto">{{ __('Start selling online in minutes. Choose a plan, create your account, and we will set up everything for you.') }}</p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 -mt-8">
        {{-- Plans --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            @foreach($plans as $plan)
                <label class="cursor-pointer" x-data>
                    <input type="radio" name="plan_preview" value="{{ $plan->id }}" class="hidden peer"
                        {{ $loop->index === 1 ? 'checked' : '' }}
                        @click="document.getElementById('plan_id').value = '{{ $plan->id }}'">
                    <div class="rounded-2xl border-2 p-6 bg-white dark:bg-zinc-900 transition-all
                        peer-checked:border-indigo-500 peer-checked:shadow-lg peer-checked:shadow-indigo-500/20
                        border-slate-200 dark:border-zinc-800 hover:border-indigo-300">
                        @if($loop->index === 1)
                            <span class="inline-block px-3 py-1 text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-full mb-3">{{ __('Most Popular') }}</span>
                        @endif
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h3>
                        <div class="mt-2">
                            <span class="text-3xl font-bold text-slate-900 dark:text-white">${{ number_format($plan->price, 0) }}</span>
                            <span class="text-slate-500">/{{ __('month') }}</span>
                        </div>
                        <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                            <li class="flex items-center gap-2">
                                <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                                {{ $plan->max_products ? number_format($plan->max_products) . ' ' . __('products') : __('Unlimited products') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                                {{ $plan->max_users ? $plan->max_users . ' ' . __('users') : __('Unlimited users') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                                {{ count($plan->modules ?? []) }} {{ __('modules') }}
                            </li>
                        </ul>
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Registration Form --}}
        <div class="max-w-2xl mx-auto bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-slate-200 dark:border-zinc-800 p-8 mb-16">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">{{ __('Create Your Account') }}</h2>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('tenant-registration.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="plan_id" id="plan_id" value="{{ $plans[1]->id ?? $plans[0]->id }}">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Store Name') }} *</label>
                    <input type="text" name="store_name" value="{{ old('store_name') }}" required
                        class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="{{ __('My Awesome Store') }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Your Name') }} *</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                            class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Phone') }}</label>
                        <input type="tel" name="owner_phone" value="{{ old('owner_phone') }}"
                            class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="+593 9XX XXX XXX">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Email') }} *</label>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}" required
                        class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Password') }} *</label>
                        <input type="password" name="password" required
                            class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Confirm Password') }} *</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Country') }} *</label>
                    <input type="text" name="country" value="{{ old('country', 'Ecuador') }}" required
                        class="w-full rounded-lg border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="flex items-start gap-2">
                    <input type="checkbox" name="accepted_terms" id="accepted_terms" required
                        class="mt-1 rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500">
                    <label for="accepted_terms" class="text-sm text-slate-600 dark:text-slate-400">
                        {{ __('I accept the') }} <a href="{{ route('legal.terms') }}" target="_blank" class="text-indigo-600 hover:underline">{{ __('Terms of Service') }}</a>
                        {{ __('and') }} <a href="{{ route('legal.privacy') }}" target="_blank" class="text-indigo-600 hover:underline">{{ __('Privacy Policy') }}</a>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all">
                    {{ __('Create My Store') }}
                </button>

                <p class="text-center text-xs text-slate-500 dark:text-slate-400">
                    {{ __('Your registration will be reviewed by our team. You will receive an email once approved.') }}
                </p>
            </form>
        </div>
    </div>
</body>
</html>
