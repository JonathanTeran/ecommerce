<x-layouts.app :title="$siteName . ' — Electronica & Tech'">
@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Rubik', sans-serif; background: #fff; }
        .preview-banner { position: fixed; top: 0; left: 0; right: 0; z-index: 9999; background: linear-gradient(90deg, #f59e0b, #d97706); color: #000; text-align: center; padding: 10px; font-size: 13px; font-weight: 600; }
        .preview-spacer { height: 42px; }
        .rd-topbar { background: #1a1a2e; color: #ccc; padding: 8px 0; font-size: 12px; }
        .rd-header { background: #fff; padding: 16px 0; border-bottom: 2px solid #ff6600; }
        .rd-logo { font-size: 24px; font-weight: 700; color: #1a1a2e; text-decoration: none; }
        .rd-logo span { color: #ff6600; }
        .rd-search { display: flex; flex: 1; max-width: 500px; margin: 0 24px; }
        .rd-search select { padding: 10px 12px; border: 2px solid #eee; border-right: none; background: #f9f9f9; font-size: 13px; }
        .rd-search input { flex: 1; padding: 10px 16px; border: 2px solid #eee; border-left: none; border-right: none; font-size: 14px; }
        .rd-search button { padding: 10px 20px; background: #ff6600; color: #fff; border: none; cursor: pointer; }
        .rd-catnav { background: #1a1a2e; padding: 0; }
        .rd-catnav a { color: #fff; text-decoration: none; font-size: 13px; padding: 12px 16px; display: inline-block; }
        .rd-catnav a:hover { background: #ff6600; }
        .rd-hero { background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 40px 0; margin-bottom: 40px; }
        .rd-hero h1 { font-size: 36px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .rd-hero p { color: #666; font-size: 16px; margin-bottom: 20px; }
        .rd-hero .btn-shop { display: inline-block; padding: 12px 32px; background: #ff6600; color: #fff; text-decoration: none; font-weight: 600; border-radius: 4px; }
        .rd-section-title { font-size: 24px; font-weight: 700; color: #1a1a2e; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 3px solid #ff6600; display: inline-block; }
        .rd-product { background: #fff; border: 1px solid #eee; padding: 16px; transition: all 0.3s; position: relative; }
        .rd-product:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-color: #ff6600; }
        .rd-product img { width: 100%; height: 200px; object-fit: contain; background: #f9f9f9; margin-bottom: 12px; }
        .rd-product .brand { font-size: 11px; color: #ff6600; text-transform: uppercase; font-weight: 600; }
        .rd-product .name { font-size: 14px; font-weight: 600; color: #333; margin: 6px 0; line-height: 1.3; height: 36px; overflow: hidden; }
        .rd-product .price { font-size: 20px; font-weight: 700; color: #ff6600; }
        .rd-product .old-price { font-size: 14px; color: #999; text-decoration: line-through; margin-left: 8px; }
        .rd-product .stock { font-size: 12px; color: #28a745; margin-top: 4px; }
        .rd-product .btn-cart { display: block; width: 100%; padding: 10px; background: #ff6600; color: #fff; border: none; font-weight: 600; cursor: pointer; margin-top: 12px; border-radius: 4px; }
        .rd-product .btn-cart:hover { background: #e55a00; }
        .rd-product .badge-sale { position: absolute; top: 8px; right: 8px; background: #dc3545; color: #fff; padding: 4px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; }
        .rd-sidebar-box { background: #1a1a2e; color: #fff; padding: 24px; margin-bottom: 24px; border-radius: 4px; }
        .rd-sidebar-box h3 { font-size: 18px; margin-bottom: 16px; color: #ff6600; }
        .rd-sidebar-box ul { list-style: none; padding: 0; }
        .rd-sidebar-box ul li { padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 14px; }
        .rd-sidebar-box ul li a { color: #ccc; text-decoration: none; }
        .rd-sidebar-box ul li a:hover { color: #ff6600; }
        .rd-brands { display: flex; justify-content: center; gap: 40px; padding: 40px 0; background: #f9f9f9; margin: 40px 0; }
        .rd-brands span { font-size: 16px; font-weight: 700; color: #bbb; text-transform: uppercase; }
        .rd-footer { background: #1a1a2e; color: #999; padding: 60px 0 30px; }
        .rd-footer h4 { color: #fff; font-size: 16px; margin-bottom: 16px; }
        .rd-footer a { color: #999; text-decoration: none; font-size: 14px; }
        .rd-footer a:hover { color: #ff6600; }
        .rd-features { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 40px 0; }
        .rd-feature { text-align: center; padding: 24px; background: #f9f9f9; border-radius: 4px; }
        .rd-feature svg { width: 40px; height: 40px; color: #ff6600; margin-bottom: 12px; }
        .rd-feature h4 { font-size: 14px; font-weight: 700; color: #333; margin-bottom: 4px; }
        .rd-feature p { font-size: 12px; color: #999; margin: 0; }
    </style>
@endpush

    {{-- Top Bar --}}
    <div class="rd-topbar">
        <div class="container" style="display: flex; justify-content: space-between;">
            <div style="display: flex; gap: 20px;">
                <span>📍 Ubicacion de la Tienda</span>
                <span>🚚 Rastrear tu Pedido</span>
                <span>📞 Llama para Consultas</span>
            </div>
            <div style="display: flex; gap: 16px;">
                <span>USD $</span>
                <span>Espanol</span>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <header class="rd-header">
        <div class="container" style="display: flex; align-items: center; justify-content: space-between;">
            <a href="#" class="rd-logo">{{ $siteName }} <span>Store</span></a>
            <div class="rd-search">
                <select><option>Todas las Categorias</option></select>
                <input type="text" placeholder="Buscar productos...">
                <button>🔍</button>
            </div>
            <div style="display: flex; gap: 20px; align-items: center; font-size: 14px;">
                <a href="#" style="color: #333; text-decoration: none;">👤 Mi Cuenta</a>
                <a href="#" style="color: #333; text-decoration: none;">❤️ Favoritos</a>
                <a href="#" style="color: #ff6600; text-decoration: none; font-weight: 600;">🛒 Carrito (0)</a>
            </div>
        </div>
    </header>

    {{-- Category Navigation --}}
    <nav class="rd-catnav">
        <div class="container">
            @foreach($categories->take(7) as $cat)
                <a href="#">{{ $cat->name }}</a>
            @endforeach
        </div>
    </nav>

    {{-- Hero --}}
    <section class="rd-hero">
        <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
            <div>
                <p style="color: #ff6600; font-weight: 600; font-size: 14px; text-transform: uppercase;">🔥 Ofertas de Temporada</p>
                <h1>Tecnologia al Mejor Precio</h1>
                <p>Laptops, componentes, perifericos y mas. Envio a todo el pais con garantia incluida.</p>
                <a href="#" class="btn-shop">Ver Ofertas →</a>
            </div>
            <div style="text-align: center;">
                @if($allProducts->first()?->primary_image_url)
                    <img src="{{ $allProducts->first()->primary_image_url }}" style="max-height: 300px; object-fit: contain;">
                @endif
            </div>
        </div>
    </section>

    {{-- Features --}}
    <div class="container">
        <div class="rd-features">
            <div class="rd-feature">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <h4>Envio Gratis</h4>
                <p>En compras +$100</p>
            </div>
            <div class="rd-feature">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h4>Garantia Oficial</h4>
                <p>6 a 12 meses</p>
            </div>
            <div class="rd-feature">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <h4>Pago Seguro</h4>
                <p>Multiples metodos</p>
            </div>
            <div class="rd-feature">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <h4>Soporte 24/7</h4>
                <p>WhatsApp y telefono</p>
            </div>
        </div>
    </div>

    {{-- Products with Sidebar --}}
    <div class="container" style="margin-bottom: 60px;">
        <h2 class="rd-section-title">Productos Destacados</h2>
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 24px;">
            {{-- Sidebar --}}
            <div>
                <div class="rd-sidebar-box">
                    <h3>Categorias</h3>
                    <ul>
                        @foreach($categories as $cat)
                            <li><a href="#">{{ $cat->name }} ({{ $cat->products_count }})</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="rd-sidebar-box">
                    <h3>Marcas</h3>
                    <ul>
                        @foreach($brands as $brand)
                            <li><a href="#">{{ $brand->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Product Grid --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                @foreach($allProducts->take(9) as $product)
                    <div class="rd-product">
                        @if($product->compare_price && $product->compare_price > $product->price)
                            <div class="badge-sale">-{{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%</div>
                        @endif
                        <img src="{{ $product->primary_image_url ?: 'https://placehold.co/300x200/f9f9f9/999?text='.urlencode($product->name) }}" alt="{{ $product->name }}">
                        @if($product->brand)
                            <div class="brand">{{ $product->brand->name }}</div>
                        @endif
                        <div class="name">{{ $product->name }}</div>
                        <div>
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                            @if($product->compare_price)
                                <span class="old-price">${{ number_format($product->compare_price, 2) }}</span>
                            @endif
                        </div>
                        <div class="stock">✓ En Stock</div>
                        <button class="btn-cart">🛒 Agregar al Carrito</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Brands Strip --}}
    <div class="rd-brands">
        @foreach($brands as $brand)
            <span>{{ $brand->name }}</span>
        @endforeach
    </div>

    {{-- Footer --}}
    <footer class="rd-footer">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px;">
                <div>
                    <h4>{{ $siteName }}</h4>
                    <p style="font-size: 14px;">Tu tienda de tecnologia de confianza. Productos originales con garantia oficial.</p>
                </div>
                <div>
                    <h4>Departamentos</h4>
                    @foreach($categories->take(5) as $cat)
                        <p><a href="#">{{ $cat->name }}</a></p>
                    @endforeach
                </div>
                <div>
                    <h4>Mi Cuenta</h4>
                    <p><a href="#">Mis Pedidos</a></p>
                    <p><a href="#">Lista de Deseos</a></p>
                    <p><a href="#">Rastrear Pedido</a></p>
                    <p><a href="#">Soporte</a></p>
                </div>
                <div>
                    <h4>Informacion</h4>
                    <p><a href="#">Sobre Nosotros</a></p>
                    <p><a href="#">Envios</a></p>
                    <p><a href="#">Devoluciones</a></p>
                    <p><a href="#">Contacto</a></p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #333; font-size: 13px;">
                &copy; {{ date('Y') }} {{ $siteName }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</x-layouts.app>
