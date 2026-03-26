import './bootstrap';

/**
 * Ecommerce Tracking Helper
 *
 * Envia eventos a dataLayer (GTM/GA4), Facebook Pixel y TikTok Pixel.
 * Los pixels se configuran desde Admin > Diseno de Tienda > SEO > Analytics y Tracking.
 *
 * Eventos soportados:
 * - view_item: al ver un producto (se dispara desde products/show.blade.php)
 * - add_to_cart: al agregar un producto al carrito
 * - remove_from_cart: al eliminar un producto del carrito
 * - begin_checkout: al entrar al checkout (se dispara desde checkout/index.blade.php)
 * - purchase: al completar la compra (se dispara desde checkout/confirmation.blade.php)
 *
 * dataLayer (GTM):
 *   Google Tag Manager lee eventos de window.dataLayer. Cada push con un "event"
 *   crea un trigger que puedes usar en GTM para enviar datos a Google Ads,
 *   Google Analytics, o cualquier otro servicio.
 *   Formato: window.dataLayer.push({ event: 'nombre_evento', ecommerce: { ... } })
 *
 * Facebook Pixel:
 *   Usa fbq('track', 'EventName', { ... }) para enviar eventos estandar.
 *
 * TikTok Pixel:
 *   Usa ttq.track('EventName', { ... }) para enviar eventos estandar.
 */
window.trackEcommerce = function(eventName, data = {}) {
    const t = window.__TRACKING__ || {};
    const currency = t.currency || 'USD';

    // --- dataLayer (GTM + GA4) ---
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ ecommerce: null }); // Clear previous ecommerce data
    window.dataLayer.push({
        event: eventName,
        ecommerce: { currency, ...data }
    });

    // --- Facebook Pixel ---
    if (t.hasFbq && typeof fbq === 'function') {
        const fbEventMap = {
            view_item: 'ViewContent',
            add_to_cart: 'AddToCart',
            remove_from_cart: null,
            begin_checkout: 'InitiateCheckout',
            purchase: 'Purchase'
        };
        const fbEvent = fbEventMap[eventName];
        if (fbEvent) {
            const fbData = { currency, value: data.value || 0 };
            if (data.items && data.items.length > 0) {
                fbData.content_ids = data.items.map(i => i.item_id);
                fbData.content_type = 'product';
                fbData.contents = data.items.map(i => ({ id: i.item_id, quantity: i.quantity || 1 }));
            }
            if (data.transaction_id) {
                fbData.order_id = data.transaction_id;
            }
            fbq('track', fbEvent, fbData);
        }
    }

    // --- TikTok Pixel ---
    if (t.hasTtq && typeof ttq !== 'undefined') {
        const ttEventMap = {
            view_item: 'ViewContent',
            add_to_cart: 'AddToCart',
            remove_from_cart: null,
            begin_checkout: 'InitiateCheckout',
            purchase: 'CompletePayment'
        };
        const ttEvent = ttEventMap[eventName];
        if (ttEvent) {
            const ttData = { currency, value: data.value || 0 };
            if (data.items && data.items.length > 0) {
                ttData.contents = data.items.map(i => ({
                    content_id: String(i.item_id),
                    content_name: i.item_name || '',
                    quantity: i.quantity || 1,
                    price: i.price || 0
                }));
                ttData.content_type = 'product';
            }
            ttq.track(ttEvent, ttData);
        }
    }
};

// Livewire v3 includes Alpine. We hook into its initialization.

document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        count: 0,
        subtotal: 0,
        discount_amount: 0,
        coupon_code: null,
        tax_amount: 0,
        total: 0,
        open: false,
        loading: false,
        sessionId: localStorage.getItem('cart_session_id'),

        async init() {
            if (this.sessionId) {
                await this.fetchCart();
            }
        },

        async fetchCart() {
            try {
                const headers = this.sessionId ? { 'X-Session-ID': this.sessionId } : {};
                const res = await fetch('/api/cart', { headers });
                const data = await res.json();

                this.sync(data);
            } catch (e) {
                console.error('Cart error:', e);
            }
        },

        async add(productId, quantity = 1) {
            this.loading = true;
            this.open = true; // Open drawer

            try {
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
                if (this.sessionId) headers['X-Session-ID'] = this.sessionId;

                const res = await fetch('/api/cart', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ product_id: productId, quantity })
                });

                const data = await res.json();
                this.sync(data);

                // Track add_to_cart event
                if (data.data && data.data.items) {
                    const addedItem = data.data.items.find(i => i.product_id === productId);
                    if (addedItem) {
                        window.trackEcommerce('add_to_cart', {
                            value: addedItem.price * quantity,
                            items: [{
                                item_id: addedItem.product_id,
                                item_name: addedItem.name,
                                price: addedItem.price,
                                quantity: quantity
                            }]
                        });
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        async update(itemId, quantity) {
            if (quantity < 1) return; // Or handle removal if qty is 0

            try {
                const res = await fetch(`/api/cart/${itemId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Session-ID': this.sessionId
                    },
                    body: JSON.stringify({ quantity })
                });
                const data = await res.json();
                this.sync(data);
            } catch (e) {
                console.error(e);
            }
        },

        async remove(itemId) {
            // Capture item data before removal for tracking
            const removedItem = this.items.find(i => i.id === itemId);

            try {
                const res = await fetch(`/api/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: { 'X-Session-ID': this.sessionId }
                });
                const data = await res.json();
                this.sync(data);

                // Track remove_from_cart event
                if (removedItem) {
                    window.trackEcommerce('remove_from_cart', {
                        value: removedItem.price * removedItem.quantity,
                        items: [{
                            item_id: removedItem.product_id,
                            item_name: removedItem.name,
                            price: removedItem.price,
                            quantity: removedItem.quantity
                        }]
                    });
                }
            } catch (e) {
                console.error(e);
            }
        },

        async applyCoupon(code) {
            try {
                const res = await fetch('/api/cart/apply-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Session-ID': this.sessionId
                    },
                    body: JSON.stringify({ code })
                });
                const data = await res.json();
                if (res.ok) {
                    this.sync(data);
                    return { success: true, message: data.message };
                }
                return { success: false, message: data.message };
            } catch (e) {
                console.error(e);
                return { success: false, message: 'Error al aplicar cupón' };
            }
        },

        async removeCoupon() {
            try {
                const res = await fetch('/api/cart/coupon', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Session-ID': this.sessionId
                    }
                });
                const data = await res.json();
                if (res.ok) {
                    this.sync(data);
                }
            } catch (e) {
                console.error(e);
            }
        },

        sync(response) {
            if (response.session_id) {
                this.sessionId = response.session_id;
                localStorage.setItem('cart_session_id', response.session_id);
            }
            if (response.data) {
                this.items = response.data.items;
                this.count = response.data.items.reduce((acc, item) => acc + item.quantity, 0);
                this.subtotal = response.data.subtotal || 0;
                this.discount_amount = response.data.discount_amount || 0;
                this.coupon_code = response.data.coupon_code || null;
                this.tax_amount = response.data.tax_amount || 0;
                this.total = response.data.total || 0;
            }
            // Cookie fallback
            document.cookie = `cart_session_id=${this.sessionId}; path=/; max-age=2592000`; // 30 days
        },

        toggle() {
            this.open = !this.open;
        }
    });
});
