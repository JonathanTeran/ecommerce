<x-layouts.app>
    <div class="bg-gray-50 dark:bg-zinc-950 min-h-screen pt-24 pb-8 md:pt-28 md:pb-16" x-data="checkoutForm({
        paymentMethodId: @js($paymentMethods->first()?->key ?? ''),
        requiresProof: @js($paymentMethods->first()?->requires_proof ?? false),
        paymentMethods: @js($paymentMethods->map(fn($m) => ['id' => $m->key, 'surcharge_percentage' => $m->surcharge_percentage, 'name' => $m->name])),
        shippingRates: @js($shippingRates),
        locale: {
            uploadProof: @js(__('Por favor suba el comprobante de pago.')),
            genericError: @js(__('Error al procesar el pedido')),
            legalRequired: @js(__('Debes aceptar los documentos legales para continuar.'))
        }
    })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6 md:mb-10 tracking-tight">{{ __('Checkout') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12">
                {{-- Left Column: Forms/Auth --}}
                <div class="lg:col-span-7 space-y-8">
                    @auth
                        @if($quotationOnlyMode)
                            @include('checkout.partials.quotation-only')
                        @else
                            @include('checkout.partials.shipping-address')
                            @include('checkout.partials.billing-address')
                            @include('checkout.partials.shipping-method')
                            @include('checkout.partials.payment-method')
                        @endif
                    @else
                        @include('checkout.partials.guest-prompt')
                    @endauth
                </div>

                {{-- Right Column: Order Summary --}}
                <div class="lg:col-span-5">
                    @include('checkout.partials.order-summary')
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutForm', (config) => ({
                loading: false,
                paymentMethodId: config.paymentMethodId,
                requiresProof: config.requiresProof,
                paymentMethods: config.paymentMethods,
                acceptedLegalDocuments: false,

                // Shipping
                shippingRateId: config.shippingRates?.length ? config.shippingRates[0].key : null,
                shippingRates: config.shippingRates || [],
                shippingCost: config.shippingRates?.length ? config.shippingRates[0].price : 0,

                // Address Forms
                sameAsShipping: true,
                shipping: {
                    name: @js(auth()->user()?->name ?? ''),
                    identity_document: '',
                    email: @js(auth()->user()?->email ?? ''),
                    address: '',
                    city: '',
                    state: '',
                    zip: '',
                    phone: ''
                },
                billing: {
                    name: '',
                    tax_id: '',
                    address: '',
                    city: '',
                    state: '',
                    zip: '',
                    phone: ''
                },

                get selectedPaymentMethod() {
                    return this.paymentMethods.find(m => m.id == this.paymentMethodId);
                },

                get surchargePercentage() {
                    return this.selectedPaymentMethod ? parseFloat(this.selectedPaymentMethod
                        .surcharge_percentage) : 0;
                },

                get cartTotal() {
                    return parseFloat(Alpine.store('cart').total || 0);
                },

                get surchargeAmount() {
                    return (this.cartTotal + this.shippingCost) * (this.surchargePercentage / 100);
                },

                get finalTotal() {
                    return this.cartTotal + this.shippingCost + this.surchargeAmount;
                },

                selectShippingRate(rateId) {
                    this.shippingRateId = rateId;
                    const rate = this.shippingRates.find(r => r.key == rateId);
                    this.shippingCost = rate ? rate.price : 0;
                },

                selectMethod(id, requiresProof) {
                    this.paymentMethodId = id;
                    this.requiresProof = requiresProof;
                },

                validateSteps() {
                    if (!this.shipping.name || !this.shipping.identity_document || !this.shipping
                        .email || !this.shipping.address || !this
                        .shipping.city || !this.shipping.phone) {
                        alert(
                            'Por favor complete todos los campos obligatorios de la dirección de envío (incluyendo Cédula/RUC).'
                        );
                        return false;
                    }

                    if (!this.sameAsShipping) {
                        if (!this.billing.name || !this.billing.tax_id || !this.billing.address || !this
                            .billing.city || !this.billing.phone) {
                            alert(
                                'Por favor complete todos los campos de la dirección de facturación.'
                            );
                            return false;
                        }
                    }

                    if (!this.acceptedLegalDocuments) {
                        alert(config.locale.legalRequired);
                        return false;
                    }

                    return true;
                },

                async placeOrder() {
                    if (!this.validateSteps()) return;

                    this.loading = true;
                    try {
                        const formData = new FormData();
                        formData.append('payment_method', this.paymentMethodId);
                        if (this.shippingRateId) {
                            formData.append('shipping_rate_key', this.shippingRateId);
                        }
                        formData.append('accepted_legal_documents', this.acceptedLegalDocuments ? '1' : '0');

                        formData.append('shipping_address[name]', this.shipping.name);
                        formData.append('shipping_address[identity_document]', this.shipping
                            .identity_document);
                        formData.append('shipping_address[email]', this.shipping.email);
                        formData.append('shipping_address[address]', this.shipping.address);
                        formData.append('shipping_address[city]', this.shipping.city);
                        formData.append('shipping_address[state]', this.shipping.state);
                        formData.append('shipping_address[zip]', this.shipping.zip);
                        formData.append('shipping_address[phone]', this.shipping.phone);

                        if (this.sameAsShipping) {
                            formData.append('billing_address[same_as_shipping]', '1');
                            formData.append('billing_address[name]', this.shipping.name);
                            formData.append('billing_address[tax_id]', this.shipping
                                .identity_document);
                            formData.append('billing_address[address]', this.shipping.address);
                            formData.append('billing_address[city]', this.shipping.city);
                            formData.append('billing_address[state]', this.shipping.state);
                            formData.append('billing_address[zip]', this.shipping.zip);
                            formData.append('billing_address[phone]', this.shipping.phone);
                        } else {
                            formData.append('billing_address[same_as_shipping]', '0');
                            formData.append('billing_address[name]', this.billing.name);
                            formData.append('billing_address[tax_id]', this.billing.tax_id);
                            formData.append('billing_address[address]', this.billing.address);
                            formData.append('billing_address[city]', this.billing.city);
                            formData.append('billing_address[state]', this.billing.state);
                            formData.append('billing_address[zip]', this.billing.zip);
                            formData.append('billing_address[phone]', this.billing.phone);
                        }

                        if (this.requiresProof) {
                            const fileInput = document.getElementById('payment_proof_file_' + this
                                .paymentMethodId);
                            if (fileInput && fileInput.files[0]) {
                                formData.append('payment_proof', fileInput.files[0]);
                            } else {
                                alert(config.locale.uploadProof);
                                this.loading = false;
                                return;
                            }
                        }

                        const cardHolder = document.getElementById('card_holder_' + this
                            .paymentMethodId);

                        if (cardHolder) {
                            const cardNumber = document.getElementById('card_number_' + this
                                .paymentMethodId).value;
                            const cardExpiry = document.getElementById('card_expiry_' + this
                                .paymentMethodId).value;
                            const cardCvc = document.getElementById('card_cvc_' + this
                                .paymentMethodId).value;

                            if (!cardHolder.value || !cardNumber || !cardExpiry || !cardCvc) {
                                alert('Por favor, complete todos los datos de la tarjeta.');
                                this.loading = false;
                                return;
                            }

                            formData.append('card_holder', cardHolder.value);
                            formData.append('card_number', cardNumber);
                            formData.append('card_expiry', cardExpiry);
                            formData.append('card_cvc', cardCvc);
                        }

                        const res = await fetch('/checkout/place-order', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Session-ID': localStorage.getItem('cart_session_id'),
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=\"csrf-token\"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok) {
                            Alpine.store('cart').items = [];
                            Alpine.store('cart').count = 0;
                            Alpine.store('cart').total = 0;
                            window.location.href = data.redirect;
                        } else if (data.action === 'login_required') {
                            window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { mode: 'login' } }));
                        } else {
                            alert('Error: ' + (data.message || 'Error desconocido'));
                        }
                    } catch (e) {
                        alert(config.locale.genericError);
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });
    </script>

    @push('head')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.trackEcommerce === 'function' && Alpine && Alpine.store('cart')) {
                var cart = Alpine.store('cart');
                var checkCart = setInterval(function() {
                    if (cart.items && cart.items.length > 0) {
                        clearInterval(checkCart);
                        window.trackEcommerce('begin_checkout', {
                            value: cart.total,
                            items: cart.items.map(function(item) {
                                return {
                                    item_id: item.product_id,
                                    item_name: item.name,
                                    price: item.price,
                                    quantity: item.quantity
                                };
                            })
                        });
                    }
                }, 500);
                setTimeout(function() { clearInterval(checkCart); }, 10000);
            }
        });
    </script>
    @endpush
</x-layouts.app>
