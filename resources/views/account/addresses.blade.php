<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ __('Mis Direcciones') }}</h1>

            {{-- Navigation --}}
            <div class="flex gap-4 mb-8 text-sm font-medium">
                <a href="{{ route('account.profile') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Perfil') }}</a>
                <a href="{{ route('account.orders') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('Pedidos') }}</a>
                <a href="{{ route('account.addresses') }}" class="text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 pb-1">{{ __('Direcciones') }}</a>
            </div>

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-6">
                    <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Existing Addresses --}}
            @if($addresses->count())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    @foreach($addresses as $address)
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 relative">
                            @if($address->is_default)
                                <span class="absolute top-3 right-3 px-2 py-0.5 bg-primary-100 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-full text-xs font-medium">{{ __('Principal') }}</span>
                            @endif
                            <p class="text-xs font-bold text-gray-400 uppercase mb-2">{{ $address->type === 'shipping' ? __('Envio') : __('Facturacion') }}</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $address->full_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $address->full_address }}</p>
                            @if($address->phone)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $address->phone }}</p>
                            @endif
                            <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">{{ __('Eliminar') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Add New Address --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-6">{{ __('Agregar Direccion') }}</h2>
                <form action="{{ route('account.addresses.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Tipo') }}</label>
                            <select name="type" class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                                <option value="shipping">{{ __('Envio') }}</option>
                                <option value="billing">{{ __('Facturacion') }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Etiqueta') }}</label>
                            <input type="text" name="label" placeholder="{{ __('Ej: Casa, Oficina') }}"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Nombre') }}</label>
                            <input type="text" name="first_name" required
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('first_name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Apellido') }}</label>
                            <input type="text" name="last_name" required
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('last_name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 md:col-span-2 space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Direccion') }}</label>
                            <input type="text" name="address_line_1" required
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('address_line_1') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Ciudad') }}</label>
                            <input type="text" name="city" required
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('city') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Provincia') }}</label>
                            <input type="text" name="province" required
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                            @error('province') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Telefono') }}</label>
                            <input type="text" name="phone"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Cedula') }}</label>
                            <input type="text" name="cedula"
                                class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_default" value="1" id="is_default"
                            class="rounded border-gray-300 dark:border-zinc-700 text-primary-600 focus:ring-primary-500">
                        <label for="is_default" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Establecer como direccion principal') }}</label>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition text-sm">
                            {{ __('Agregar Direccion') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
