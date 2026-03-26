<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ __('Mi Cuenta') }}</h1>

            {{-- Navigation --}}
            <div class="flex gap-4 mb-8 text-sm font-medium">
                <a href="{{ route('account.profile') }}" class="text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 pb-1">{{ __('Perfil') }}</a>
                <a href="{{ route('account.orders') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Pedidos') }}</a>
                <a href="{{ route('account.addresses') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Direcciones') }}</a>
            </div>

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-6">
                    <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Profile Info --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 mb-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-6">{{ __('Informacion Personal') }}</h2>
                <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Nombre Completo') }}</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Correo Electronico') }}</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 bg-gray-50 cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Telefono') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Cedula / RUC') }}</label>
                            <input type="text" name="identification_number" value="{{ old('identification_number', $user->identification_number) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition text-sm">
                            {{ __('Guardar Cambios') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Change Password --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-6">{{ __('Cambiar Contraseña') }}</h2>
                <form action="{{ route('account.password') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Contraseña Actual') }}</label>
                        <input type="password" name="current_password"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        @error('current_password') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Nueva Contraseña') }}</label>
                            <input type="password" name="password"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('password') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Confirmar Contraseña') }}</label>
                            <input type="password" name="password_confirmation"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-gray-800 dark:bg-zinc-700 text-white rounded-xl font-bold hover:bg-gray-900 dark:hover:bg-zinc-600 transition text-sm">
                            {{ __('Actualizar Contraseña') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
