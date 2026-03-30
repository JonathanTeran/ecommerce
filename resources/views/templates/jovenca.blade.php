<x-layouts.app :title="$siteName . ' — Joyeria & Accesorios'">
@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif !important; color: #333; }
        h1, h2, h3, h4, .font-heading { font-family: 'Playfair Display', serif !important; }
        .jv-hero { background: #f8f5f0; padding: 80px 0; }
        .jv-hero h1 { font-size: 44px; font-weight: 400; color: #1a1a1a; line-height: 1.2; margin-bottom: 16px; }
        .jv-hero p { color: #888; font-size: 16px; margin-bottom: 30px; }
        .jv-hero .btn-gold { display: inline-block; padding: 14px 40px; border: 2px solid #1a1a1a; color: #1a1a1a; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .jv-hero .btn-gold:hover { background: #1a1a1a; color: #fff; }
        .jv-section-title { text-align: center; margin: 60px 0 40px; }
        .jv-section-title h2 { font-size: 32px; font-weight: 400; color: #1a1a1a; margin-bottom: 8px; }
        .jv-section-title p { color: #999; font-size: 14px; }
        .jv-section-title .line { width: 60px; height: 2px; background: #c8a165; margin: 16px auto 0; }
        .jv-product { background: #fff; transition: all 0.3s; border: 1px solid #f0f0f0; }
        .jv-product:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .jv-product img { width: 100%; height: 280px; object-fit: cover; background: #f8f5f0; }
        .jv-product .info { padding: 20px; text-align: center; }
        .jv-product .brand { font-size: 11px; color: #c8a165; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; }
        .jv-product .name { font-family: 'Playfair Display', serif; font-size: 16px; color: #1a1a1a; margin-bottom: 10px; }
        .jv-product .price { font-size: 18px; font-weight: 600; color: #1a1a1a; }
        .jv-product .old-price { color: #ccc; text-decoration: line-through; font-size: 14px; margin-left: 8px; }
        .jv-product .btn-cart { display: inline-block; padding: 10px 24px; border: 1px solid #1a1a1a; background: transparent; color: #1a1a1a; text-transform: uppercase; font-size: 11px; letter-spacing: 2px; cursor: pointer; margin-top: 12px; text-decoration: none; transition: all 0.3s; }
        .jv-product .btn-cart:hover { background: #1a1a1a; color: #fff; }
        .jv-categories { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .jv-cat-card { position: relative; height: 200px; background: #f8f5f0; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid #eee; transition: all 0.3s; text-decoration: none; }
        .jv-cat-card:hover { border-color: #c8a165; }
        .jv-cat-card h3 { font-size: 20px; color: #1a1a1a; margin: 0 0 4px; }
        .jv-cat-card span { font-size: 12px; color: #c8a165; text-transform: uppercase; letter-spacing: 1px; }
        .jv-brands { display: flex; justify-content: center; gap: 50px; padding: 50px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; flex-wrap: wrap; }
        .jv-brands a { font-size: 14px; color: #ccc; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; text-decoration: none; transition: color 0.3s; }
        .jv-brands a:hover { color: #c8a165; }
        .jv-cta { background: #1a1a1a; color: #fff; text-align: center; padding: 80px 0; }
        .jv-cta h2 { font-size: 36px; font-weight: 400; margin-bottom: 16px; color: #fff; }
        .jv-cta p { color: #999; margin-bottom: 30px; }
        .jv-cta .btn-gold-outline { display: inline-block; padding: 14px 40px; border: 1px solid #c8a165; color: #c8a165; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; text-decoration: none; }
    </style>
@endpush

    {{-- Hero --}}
    <section class="jv-hero">
        <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
            <div>
                <p style="font-size: 12px; color: #c8a165; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 16px;">Coleccion Exclusiva</p>
                <h1>Descubre Nuestra Seleccion Premium</h1>
                <p>Productos de la mas alta calidad, seleccionados cuidadosamente para ti.</p>
                <a href="{{ route('shop.index') }}" class="btn-gold">Ver Coleccion</a>
            </div>
            <div style="background: #ede8e0; height: 400px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                @if($allProducts->first()?->primary_image_url)
                    <img src="{{ $allProducts->first()->primary_image_url }}" style="max-height: 380px; object-fit: contain;">
                @else
                    <span style="color: #b8a88a; font-size: 14px;">Agrega productos desde el panel</span>
                @endif
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <div class="container">
        <div class="jv-section-title">
            <h2>Categorias Populares</h2>
            <p>Explora nuestra seleccion por categoria</p>
            <div class="line"></div>
        </div>
        <div class="jv-categories">
            @foreach($categories->take(6) as $cat)
                <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="jv-cat-card">
                    <h3>{{ $cat->name }}</h3>
                    <span>{{ $cat->products_count }} productos</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Products --}}
    <div class="container">
        <div class="jv-section-title">
            <h2>Nuestra Seleccion</h2>
            <p>Productos seleccionados especialmente para ti</p>
            <div class="line"></div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 60px;">
            @foreach($allProducts->take(8) as $product)
                <div class="jv-product">
                    <a href="{{ route('products.show', $product) }}">
                        <img src="{{ $product->primary_image_url ?: 'https://placehold.co/400x400/f8f5f0/c8a165?text='.urlencode($product->name) }}" alt="{{ $product->name }}">
                    </a>
                    <div class="info">
                        @if($product->brand)
                            <div class="brand">{{ $product->brand->name }}</div>
                        @endif
                        <div class="name"><a href="{{ route('products.show', $product) }}" style="color: inherit; text-decoration: none;">{{ $product->name }}</a></div>
                        <div>
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                            @if($product->compare_price)
                                <span class="old-price">${{ number_format($product->compare_price, 2) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="btn-cart">Ver Producto</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brands --}}
    <div class="jv-brands">
        @foreach($brands as $brand)
            <a href="{{ route('shop.index', ['brand' => $brand->slug]) }}">{{ $brand->name }}</a>
        @endforeach
    </div>

    {{-- CTA --}}
    <section class="jv-cta">
        <h2>Explora Toda Nuestra Coleccion</h2>
        <p>Encuentra el producto perfecto para ti</p>
        <a href="{{ route('shop.index') }}" class="btn-gold-outline">Ver Tienda Completa</a>
    </section>

</x-layouts.app>
