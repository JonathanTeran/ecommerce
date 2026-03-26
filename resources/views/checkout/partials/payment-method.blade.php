<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8">
    <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 flex items-center gap-3">
        <span
            class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-600 text-sm font-bold" x-text="shippingRates.length > 0 ? '4' : '3'"></span>
        {{ __('Método de Pago') }}
    </h2>

    <div class="space-y-4">
        @forelse($paymentMethods as $index => $method)
            <div class="border rounded-xl cursor-pointer transition-all"
                :class="paymentMethodId == '{{ $method->key }}' ?
                    'border-primary-500 bg-primary-50 dark:bg-primary-900/10' :
                    'border-gray-200 dark:border-zinc-700 hover:border-gray-300'"
                @click="selectMethod('{{ $method->key }}', {{ $method->requires_proof ? 'true' : 'false' }})">

                <label class="flex items-center gap-4 p-4 cursor-pointer">
                    <input type="radio" name="payment_method" value="{{ $method->key }}"
                        :checked="paymentMethodId == '{{ $method->key }}'"
                        @change="selectMethod('{{ $method->key }}', {{ $method->requires_proof ? 'true' : 'false' }})"
                        class="text-primary-600 focus:ring-primary-500">
                    <div class="flex-1">
                        <p class="font-bold text-gray-900 dark:text-white">{{ $method->name }}</p>
                        @if ($method->surcharge_percentage > 0)
                            <p class="text-xs text-amber-600 font-medium">
                                {{ __('Recargo') }}:
                                {{ number_format($method->surcharge_percentage, 2) }}%
                            </p>
                        @endif
                    </div>
                </label>

                <div x-show="paymentMethodId == '{{ $method->key }}'" x-cloak
                    class="px-4 pb-4 pt-0 text-sm text-gray-600 dark:text-gray-300 space-y-4">

                    @if (\Illuminate\Support\Str::contains($method->slug, 'nuvei'))
                        <div
                            class="mt-4 p-4 bg-gray-50 dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="w-5 h-5 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                    </path>
                                </svg>
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Detalles de la Tarjeta') }}</span>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">{{ __('Titular de la Tarjeta') }}</label>
                                    <input type="text" id="card_holder_{{ $method->key }}"
                                        placeholder="NOMBRE COMO APARECE EN LA TARJETA"
                                        class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5">
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">{{ __('Número de Tarjeta') }}</label>
                                    <div class="relative">
                                        <input type="text"
                                            id="card_number_{{ $method->key }}"
                                            placeholder="0000 0000 0000 0000" maxlength="19"
                                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5 pl-10">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">{{ __('Vencimiento') }}</label>
                                        <input type="text"
                                            id="card_expiry_{{ $method->key }}"
                                            placeholder="MM/YY" maxlength="5"
                                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5 text-center">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">{{ __('CVC / CVV') }}</label>
                                        <div class="relative">
                                            <input type="text"
                                                id="card_cvc_{{ $method->key }}"
                                                placeholder="123" maxlength="4"
                                                class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5 pl-10">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-4 w-4 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                                    </path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ... (Rest of existing content) ... -->

                    @if ($method->instructions)
                        <div class="prose prose-sm dark:prose-invert pl-0 md:pl-8">
                            {!! $method->instructions !!}
                        </div>
                    @endif

                    @if ($method->requires_proof)
                        <div
                            class="ml-0 md:ml-8 bg-amber-50 dark:bg-amber-900/20 p-3 md:p-4 rounded-lg border border-amber-200 dark:border-amber-800">
                            <label
                                class="block text-sm font-medium mb-2 text-amber-800 dark:text-amber-200">
                                <svg class="w-4 h-4 inline mr-1" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                {{ __('Subir Comprobante de Pago (Imagen/PDF)') }}
                            </label>
                            <input type="file" id="payment_proof_file_{{ $method->key }}"
                                class="block w-full text-sm text-gray-700 dark:text-gray-300
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-100 file:text-primary-700
                                hover:file:bg-primary-200
                                dark:file:bg-primary-900 dark:file:text-primary-300"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                                {{ __('* Requerido para completar el pedido') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">{{ __('No hay métodos de pago disponibles.') }}</p>
        @endforelse
    </div>

    {{-- Place Order Button --}}
    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-zinc-800">
        <label class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300 mb-4">
            <input type="checkbox" x-model="acceptedLegalDocuments"
                class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <span>
                {{ __('Acepto los') }}
                <a href="{{ route('legal.terms') }}" target="_blank"
                    class="font-semibold text-primary-600 hover:text-primary-500">{{ __('Términos de Servicio') }}</a>,
                <a href="{{ route('legal.privacy') }}" target="_blank"
                    class="font-semibold text-primary-600 hover:text-primary-500">{{ __('Política de Privacidad') }}</a>
                {{ __('y la') }}
                <a href="{{ route('legal.acceptable-use') }}" target="_blank"
                    class="font-semibold text-primary-600 hover:text-primary-500">{{ __('Política de Uso Aceptable') }}</a>.
            </span>
        </label>

        <button @click="placeOrder()"
            :disabled="loading || !paymentMethodId || !acceptedLegalDocuments"
            class="w-full bg-primary-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-primary-500/30 hover:bg-primary-700 hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg x-show="loading" x-cloak class="animate-spin h-5 w-5" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span x-show="!loading">{{ __('Realizar Pedido') }}</span>
            <span x-show="loading" x-cloak>{{ __('Procesando...') }}</span>
        </button>

        @if($quotationsEnabled ?? false)
            <div class="mt-3 text-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('o') }}</span>
            </div>

            <a href="{{ route('quotation.create') }}"
                class="mt-3 w-full inline-flex items-center justify-center py-3 border-2 border-primary-600 text-primary-600 dark:text-primary-400 rounded-xl font-bold text-base hover:bg-primary-50 dark:hover:bg-primary-900/10 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('Solicitar Cotización') }}
            </a>
        @endif
    </div>
</div>
