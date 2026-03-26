<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Email Verified') }} - {{ config('app.name') }}</title>
    {{ \App\Support\ViteAssets::tags(['resources/css/app.css']) }}
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-slate-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md mx-auto text-center px-4">
        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <x-heroicon-s-check-circle class="w-8 h-8 text-emerald-600" />
        </div>
        <h1 class="text-2xl font-bold mb-3">{{ __('Email Verified!') }}</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-6">
            {{ __('Your email has been verified successfully. Our team is now reviewing your registration. You will receive an email once your store is approved and ready to use.') }}
        </p>
        <p class="text-sm text-slate-500">{{ __('This process usually takes less than 24 hours.') }}</p>
        <a href="{{ route('home') }}" class="inline-block mt-8 text-indigo-600 hover:text-indigo-500 text-sm font-medium">&larr; {{ __('Back to home') }}</a>
    </div>
</body>
</html>
