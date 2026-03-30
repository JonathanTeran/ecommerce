<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteName }} — Joyeria & Accesorios</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/templates/jovenca-demo/css/bootstrap.css">
    <link rel="stylesheet" href="/templates/jovenca-demo/style.css">
    <style>
        body { font-family: 'DM Sans', sans-serif; color: #333; background: #fff; }
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        .preview-banner { position: fixed; top: 0; left: 0; right: 0; z-index: 9999; background: linear-gradient(90deg, #f59e0b, #d97706); color: #000; text-align: center; padding: 10px; font-size: 13px; font-weight: 600; }
        .preview-spacer { height: 42px; }
        .jv-topbar { background: #1a1a1a; color: #fff; padding: 8px 0; font-size: 13px; }
        .jv-navbar { padding: 24px 0; border-bottom: 1px solid #eee; }
        .jv-logo { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a1a1a; text-decoration: none; letter-spacing: 3px; text-transform: uppercase; }
        .jv-nav-links { display: flex; gap: 32px; }
        .jv-nav-links a { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #333; text-decoration: none; font-weight: 500; }
        .jv-nav-links a:hover { color: #c8a165; }
        .jv-hero { background: #f8f5f0; padding: 80px 0; }
        .jv-hero h1 { font-size: 44px; font-weight: 400; color: #1a1a1a; line-height: 1.2; margin-bottom: 16px; }
        .jv-hero p { color: #888; font-size: 16px; margin-bottom: 30px; }
        .jv-hero .btn-gold { display: inline-block; padding: 14px 40px; border: 2px solid #1a1a1a; color: #1a1a1a; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; text-decoration: none; font-weight: 600; }
        .jv-hero .btn-gold:hover { background: #1a1a1a; color: #fff; }
        .jv-section-title { text-align: center; margin: 60px 0 40px; }
        .jv-section-title h2 { font-size: 32px; font-weight: 400; color: #1a1a1a; margin-bottom: 8px; }
        .jv-section-title p { color: #999; font-size: 14px; }
        .jv-section-title .line { width: 60px; height: 2px; background: #c8a165; margin: 16px auto 0; }
        .jv-product { background: #fff; transition: all 0.3s; }
        .jv-product:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .jv-product img { width: 100%; height: 320px; object-fit: cover; background: #f8f5f0; }
        .jv-product .info { padding: 20px; text-align: center; }
        .jv-product .brand { font-size: 11px; color: #c8a165; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; }
        .jv-product .name { font-family: 'Playfair Display', serif; font-size: 16px; color: #1a1a1a; margin-bottom: 10px; }
        .jv-product .price { font-size: 18px; font-weight: 600; color: #1a1a1a; }
        .jv-product .old-price { color: #ccc; text-decoration: line-through; font-size: 14px; margin-left: 8px; }
        .jv-product .btn-cart { display: block; width: 100%; padding: 12px; border: 1px solid #1a1a1a; background: transparent; color: #1a1a1a; text-transform: uppercase; font-size: 11px; letter-spacing: 2px; cursor: pointer; margin-top: 12px; }
        .jv-product .btn-cart:hover { background: #1a1a1a; color: #fff; }
        .jv-categories { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .jv-cat-card { position: relative; height: 240px; background: #f8f5f0; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid #eee; transition: all 0.3s; }
        .jv-cat-card:hover { border-color: #c8a165; }
        .jv-cat-card h3 { font-size: 20px; color: #1a1a1a; margin: 0 0 4px; }
        .jv-cat-card span { font-size: 12px; color: #c8a165; text-transform: uppercase; letter-spacing: 1px; }
        .jv-brands { display: flex; justify-content: center; gap: 50px; padding: 50px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }
        .jv-brands span { font-size: 14px; color: #ccc; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; }
        .jv-cta { background: #1a1a1a; color: #fff; text-align: center; padding: 80px 0; }
        .jv-cta h2 { font-size: 36px; font-weight: 400; margin-bottom: 16px; }
        .jv-cta p { color: #999; margin-bottom: 30px; }
        .jv-cta .btn-gold-outline { display: inline-block; padding: 14px 40px; border: 1px solid #c8a165; color: #c8a165; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; text-decoration: none; }
        .jv-footer { background: #111; color: #777; padding: 60px 0 30px; }
        .jv-footer h4 { color: #fff; font-family: 'Playfair Display', serif; font-size: 18px; margin-bottom: 20px; }
        .jv-footer a { color: #777; text-decoration: none; font-size: 14px; }
        .jv-footer a:hover { color: #c8a165; }
    </style>
</head>
<body>
    @if($isPreview ?? false)
        <div class="preview-banner">MODO VISTA PREVIA — Los cambios no han sido guardados</div>
        <div class="preview-spacer"></div>
    @endif

    {{-- Top Bar --}}
    <div class="jv-topbar">
        <div class="container" style="display: flex; justify-content: space-between;">
            <span>Envio gratuito en compras +$100 | Garantia de calidad</span>
            <span>Atencion al cliente: WhatsApp</span>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="jv-navbar">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="jv-nav-links">
                <a href="#">Inicio</a>
                <a href="#">Tienda</a>
            </div>
            <a href="#" class="jv-logo">{{ $siteName }}</a>
            <div class="jv-nav-links">
                <a href="#">Categorias</a>
                <a href="#">Contacto</a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="jv-hero">
        <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
            <div>
                <p style="font-size: 12px; color: #c8a165; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 16px;">Coleccion Exclusiva</p>
                <h1>Descubre Nuestra Seleccion Premium</h1>
                <p>Productos de la mas alta calidad, seleccionados cuidadosamente para ti.</p>
                <a href="#" class="btn-gold">Ver Coleccion</a>
            </div>
            <div style="background: #ede8e0; height: 400px; display: flex; align-items: center; justify-content: center;">
                @if($allProducts->first()?->primary_image_url)
                    <img src="{{ $allProducts->first()->primary_image_url }}" style="max-height: 380px; object-fit: contain;">
                @else
                    <span style="color: #b8a88a; font-size: 14px;">Imagen del Producto</span>
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
                <div class="jv-cat-card">
                    <h3>{{ $cat->name }}</h3>
                    <span>{{ $cat->products_count }} productos</span>
                </div>
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
                    <img src="{{ $product->primary_image_url ?: 'https://placehold.co/400x400/f8f5f0/c8a165?text='.urlencode($product->name) }}" alt="{{ $product->name }}">
                    <div class="info">
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
                        <button class="btn-cart">Agregar al Carrito</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brands --}}
    <div class="jv-brands">
        @foreach($brands as $brand)
            <span>{{ $brand->name }}</span>
        @endforeach
    </div>

    {{-- CTA --}}
    <section class="jv-cta">
        <h2>Suscribete a Nuestro Newsletter</h2>
        <p>Recibe ofertas exclusivas y novedades directamente en tu correo</p>
        <a href="#" class="btn-gold-outline">Suscribirme</a>
    </section>

    {{-- Footer --}}
    <footer class="jv-footer">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px;">
                <div>
                    <h4>{{ $siteName }}</h4>
                    <p style="font-size: 14px;">Productos premium con garantia de calidad y atencion personalizada.</p>
                </div>
                <div>
                    <h4>Categorias</h4>
                    @foreach($categories->take(4) as $cat)
                        <p><a href="#">{{ $cat->name }}</a></p>
                    @endforeach
                </div>
                <div>
                    <h4>Informacion</h4>
                    <p><a href="#">Sobre Nosotros</a></p>
                    <p><a href="#">Envios y Devoluciones</a></p>
                    <p><a href="#">Contacto</a></p>
                </div>
                <div>
                    <h4>Contacto</h4>
                    <p style="font-size: 14px;">Atencion personalizada via WhatsApp y correo electronico.</p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #333; font-size: 13px;">
                &copy; {{ date('Y') }} {{ $siteName }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</body>
</html>
