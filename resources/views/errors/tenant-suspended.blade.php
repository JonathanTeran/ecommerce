<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tienda Suspendida</title>
    {{ \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) }}
</head>

<body
    class="antialiased bg-white dark:bg-zinc-950 text-gray-800 dark:text-gray-200 h-screen flex flex-col items-center justify-center font-sans p-4">
    <div class="max-w-2xl w-full text-center space-y-8">
        {{-- Icon --}}
        <div class="flex justify-center">
            <div class="w-32 h-32 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                <svg class="w-16 h-16 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
        </div>

        {{-- Title --}}
        <h1 class="text-4xl font-black text-amber-600 dark:text-amber-500">
            Tienda Suspendida
        </h1>

        {{-- Message --}}
        <div class="space-y-4">
            @if ($message)
                <p class="text-lg text-gray-600 dark:text-gray-300">{{ $message }}</p>
            @else
                <p class="text-lg text-gray-600 dark:text-gray-300">
                    Esta tienda se encuentra temporalmente suspendida.
                </p>
                <p class="text-gray-500 dark:text-gray-400">
                    Si eres el administrador de esta tienda, contacta al soporte para mas informacion.
                </p>
            @endif
        </div>

        @if (isset($tenant) && $tenant?->name)
            <div class="pt-4">
                <span
                    class="inline-flex items-center rounded-full bg-gray-100 dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $tenant->name }}
                </span>
            </div>
        @endif
    </div>
</body>

</html>
