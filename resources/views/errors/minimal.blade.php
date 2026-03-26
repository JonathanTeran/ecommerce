<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    {{ \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) }}
</head>

<body
    class="antialiased bg-white dark:bg-zinc-950 text-gray-800 dark:text-gray-200 h-screen flex flex-col items-center justify-center font-sans p-4">
    <div class="max-w-2xl w-full text-center space-y-8">
        {{-- Dog Image --}}
        <div class="flex justify-center">
            <img src="{{ asset('images/errors/dog.png') }}" alt="Cute Dog"
                class="h-64 md:h-80 object-contain mx-auto mix-blend-multiply dark:mix-blend-normal">
        </div>

        {{-- Error Code --}}
        <h1 class="text-6xl font-black text-indigo-600 dark:text-indigo-500">
            @yield('code')
        </h1>

        {{-- Message --}}
        <div class="space-y-2">
            <h2 class="text-2xl font-bold">@yield('message')</h2>
            <p class="text-gray-500 dark:text-gray-400">@yield('description')</p>
        </div>

        {{-- Actions --}}
        <div class="flex justify-center gap-4 pt-4">
            <a href="{{ url('/') }}"
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Volver al Inicio
            </a>
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-zinc-700 text-base font-medium rounded-full text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Regresar
            </a>
        </div>

        <div class="mt-8 text-sm text-gray-400">
            <p>¿No encontraste lo que buscabas? Intenta buscar en nuestra <a href="{{ route('shop.index') }}"
                    class="text-indigo-600 hover:underline">Tienda</a>.</p>
        </div>
    </div>
</body>

</html>
