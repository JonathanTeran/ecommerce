<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-20 pb-12" x-data="quotationForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Page Header --}}
            <div class="mb-6 md:mb-10">
                <a href="{{ url('/shop') }}" class="inline-flex items-center gap-1.5 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 mb-3 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    {{ __('Volver a la Tienda') }}
                </a>
                <h1 class="font-heading text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">
                    {{ __('Solicitar Cotización') }}
                </h1>
                <p class="mt-2 text-sm sm:text-base text-gray-500 dark:text-gray-400">
                    {{ __('Completa los datos para solicitar una cotización personalizada.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12">
                {{-- Left Column: Form --}}
                <div class="lg:col-span-7 space-y-5 md:space-y-8">
                    @auth
                        {{-- Customer Info --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8">
                            <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-sm font-bold">1</span>
                                {{ __('Datos del Solicitante') }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Nombre Completo') }} *</label>
                                    <input type="text" x-model="form.customer_name"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Correo Electrónico') }} *</label>
                                    <input type="email" x-model="form.customer_email"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Teléfono') }} *</label>
                                    <input type="text" x-model="form.customer_phone"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Empresa') }}</label>
                                    <input type="text" x-model="form.customer_company" placeholder="{{ __('Opcional') }}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8">
                            <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-sm font-bold">2</span>
                                {{ __('Dirección de Envío') }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div class="md:col-span-2 space-y-1.5" x-data="{ focused: false }">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Nombre del Destinatario') }} *</label>
                                    <input type="text" x-model="form.shipping_address.name"
                                        @focus="focused = true" @blur="focused = false"
                                        :class="{'ring-2 ring-primary-500 border-primary-500': focused || form.shipping_address.name}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="md:col-span-2 space-y-1.5" x-data="{ focused: false }">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Dirección') }} *</label>
                                    <input type="text" x-model="form.shipping_address.address"
                                        @focus="focused = true" @blur="focused = false"
                                        :class="{'ring-2 ring-primary-500 border-primary-500': focused || form.shipping_address.address}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5" x-data="{ focused: false }">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Ciudad') }} *</label>
                                    <input type="text" x-model="form.shipping_address.city"
                                        @focus="focused = true" @blur="focused = false"
                                        :class="{'ring-2 ring-primary-500 border-primary-500': focused || form.shipping_address.city}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5" x-data="{ focused: false }">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Provincia') }} *</label>
                                    <input type="text" x-model="form.shipping_address.state"
                                        @focus="focused = true" @blur="focused = false"
                                        :class="{'ring-2 ring-primary-500 border-primary-500': focused || form.shipping_address.state}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5" x-data="{ focused: false }">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Código Postal') }} *</label>
                                    <input type="text" x-model="form.shipping_address.zip"
                                        @focus="focused = true" @blur="focused = false"
                                        :class="{'ring-2 ring-primary-500 border-primary-500': focused || form.shipping_address.zip}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1.5" x-data="{ focused: false }">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Teléfono') }} *</label>
                                    <input type="text" x-model="form.shipping_address.phone"
                                        @focus="focused = true" @blur="focused = false"
                                        :class="{'ring-2 ring-primary-500 border-primary-500': focused || form.shipping_address.phone}"
                                        class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8">
                            <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-sm font-bold">3</span>
                                {{ __('Notas Adicionales') }}
                            </h2>
                            <textarea x-model="form.customer_notes" rows="4" placeholder="{{ __('Detalles adicionales sobre la cotización, plazos de entrega, cantidades especiales...') }}"
                                class="w-full rounded-xl bg-gray-50 text-gray-900 border border-gray-200 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"></textarea>
                        </div>
                    @else
                        {{-- Guest Login Prompt --}}
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8 md:p-12 text-center">
                            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-5">
                                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Inicia Sesión') }}</h2>
                            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">{{ __('Debes iniciar sesión para solicitar una cotización personalizada.') }}</p>
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition shadow-lg shadow-primary-500/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                {{ __('Iniciar Sesión') }}
                            </a>
                        </div>
                    @endauth
                </div>

                {{-- Right Column: Cart Summary --}}
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5 md:p-8 lg:sticky lg:top-24">
                        <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-4 md:mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('Resumen de la Cotización') }}
                        </h2>

                        {{-- Items --}}
                        <div x-data="{ cart: Alpine.store('cart') }">
                            {{-- Empty Cart State --}}
                            <div x-show="!cart.items || cart.items.length === 0" class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-300 dark:text-zinc-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('Tu carrito está vacío.') }}</p>
                                <a href="{{ url('/shop') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 transition">
                                    {{ __('Agregar productos') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>

                            {{-- Cart Items --}}
                            <div x-show="cart.items && cart.items.length > 0">
                                <div class="space-y-3 max-h-[250px] md:max-h-[350px] overflow-y-auto mb-4 pr-1">
                                    <template x-for="item in cart.items" :key="item.id">
                                        <div class="flex gap-3 pb-3 border-b border-gray-100 dark:border-zinc-800 last:border-0 last:pb-0">
                                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg bg-gray-100 dark:bg-zinc-800 overflow-hidden shrink-0">
                                                <img :src="item.image || 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23f1f5f9%27 width=%27100%27 height=%27100%27/%3E%3C/svg%3E'"
                                                    :alt="item.name" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 dark:text-white text-sm truncate" x-text="item.name"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    <span x-text="item.quantity"></span> x $<span x-text="parseFloat(item.price).toFixed(2)"></span>
                                                </p>
                                            </div>
                                            <p class="font-semibold text-gray-900 dark:text-white text-sm whitespace-nowrap">
                                                $<span x-text="parseFloat(item.subtotal).toFixed(2)"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>

                                {{-- Totals --}}
                                <div class="space-y-2 border-t border-gray-200 dark:border-zinc-700 pt-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                                        <span class="text-gray-900 dark:text-white">$<span x-text="parseFloat(cart.subtotal).toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">{{ __('IVA (estimado)') }}</span>
                                        <span class="text-gray-900 dark:text-white">$<span x-text="parseFloat(cart.tax_amount).toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-zinc-700">
                                        <span class="text-base font-bold text-gray-900 dark:text-white">{{ __('Total Estimado') }}</span>
                                        <span class="text-xl font-bold text-gray-900 dark:text-white">$<span x-text="parseFloat(cart.total).toFixed(2)"></span></span>
                                    </div>
                                </div>

                                @auth
                                    <button @click="submitQuotation()" :disabled="submitting || !cart.items || cart.items.length === 0"
                                        class="mt-6 w-full py-3.5 md:py-4 bg-primary-600 text-white rounded-xl font-bold text-base md:text-lg hover:bg-primary-700 transition shadow-lg shadow-primary-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                        <span x-show="!submitting" class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ __('Solicitar Cotización') }}
                                        </span>
                                        <span x-show="submitting" x-cloak class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            {{ __('Enviando...') }}
                                        </span>
                                    </button>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-3">
                                        {{ __('Los precios son estimados. La cotización final será confirmada por nuestro equipo.') }}
                                    </p>
                                @endauth
                            </div>
                        </div>

                        {{-- Error Messages --}}
                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-red-600 dark:text-red-400" x-text="error"></p>
                            </div>
                        </div>

                        {{-- Info Badges --}}
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Sin compromiso de compra') }}
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Respuesta en 24-48 horas') }}
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                {{ __('Precios personalizados para tu proyecto') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quotationForm() {
            @php
                $user = auth()->user();
                $tenant = app('current_tenant');
                
                $shipping = $user?->addresses()
                    ->where('type', 'shipping')
                    ->where('is_default', true)
                    ->where('tenant_id', $tenant?->id)
                    ->first();
                    
                $billing = $user?->addresses()
                    ->where('type', 'billing')
                    ->where('is_default', true)
                    ->where('tenant_id', $tenant?->id)
                    ->first();
                    
                $customerName = $user?->name ?? '';
                $customerEmail = $user?->email ?? '';
                $customerPhone = $shipping?->phone ?? $user?->phone ?? '';
                $customerCompany = $billing?->company ?? '';
                
                $shippingName = $shipping ? $shipping->full_name : ($user?->name ?? '');
                $shippingAddress = $shipping?->address_line_1 ?? '';
                $shippingCity = $shipping?->city ?? '';
                $shippingState = $shipping?->province ?? '';
                $shippingZip = $shipping?->postal_code ?? '';
                $shippingPhone = $shipping?->phone ?? $user?->phone ?? '';
            @endphp
            return {
                submitting: false,
                error: null,
                form: {
                    customer_name: @json($customerName),
                    customer_email: @json($customerEmail),
                    customer_phone: @json($customerPhone),
                    customer_company: @json($customerCompany),
                    customer_notes: '',
                    shipping_address: {
                        name: @json($shippingName),
                        address: @json($shippingAddress),
                        city: @json($shippingCity),
                        state: @json($shippingState),
                        zip: @json($shippingZip),
                        phone: @json($shippingPhone),
                    },
                },

                async submitQuotation() {
                    this.submitting = true;
                    this.error = null;

                    try {
                        const sessionId = document.cookie.split(';').find(c => c.trim().startsWith('cart_session_id='))?.split('=')[1] || Alpine.store('cart').session_id;

                        const response = await fetch('/api/quotation', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                                'X-Session-ID': sessionId,
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (data.errors) {
                                this.error = Object.values(data.errors).flat().join(', ');
                            } else {
                                this.error = data.message || 'Error al enviar la cotización';
                            }
                            return;
                        }

                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } catch (e) {
                        this.error = 'Error de conexión. Intente nuevamente.';
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
</x-layouts.app>

