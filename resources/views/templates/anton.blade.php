<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteName }} — Moda & Estilo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/templates/anton-demo/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/templates/anton-demo/assets/css/style.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .preview-banner { position: fixed; top: 0; left: 0; right: 0; z-index: 9999; background: linear-gradient(90deg, #f59e0b, #d97706); color: #000; text-align: center; padding: 10px; font-size: 13px; font-weight: 600; }
        .preview-spacer { height: 42px; }
        .real-product-card { background: #fff; border: 1px solid #eee; transition: all 0.3s; overflow: hidden; }
        .real-product-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); transform: translateY(-4px); }
        .real-product-card img { width: 100%; height: 280px; object-fit: cover; }
        .real-product-card .info { padding: 16px; }
        .real-product-card .brand { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 4px; }
        .real-product-card .name { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px; line-height: 1.3; }
        .real-product-card .price { font-size: 18px; font-weight: 700; color: #000; }
        .real-product-card .old-price { font-size: 13px; color: #999; text-decoration: line-through; margin-left: 8px; }
        .real-product-card .btn-add { display: block; width: 100%; padding: 10px; background: #000; color: #fff; text-align: center; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; border: none; cursor: pointer; }
        .real-product-card .btn-add:hover { background: #333; }
        .section-title { text-align: center; font-size: 28px; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; margin: 50px 0 30px; color: #333; }
        .section-title span { font-weight: 600; }
        .category-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 60px; }
        .category-card { position: relative; height: 200px; overflow: hidden; background: #f5f5f5; display: flex; align-items: center; justify-content: center; }
        .category-card .overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; }
        .category-card h3 { font-size: 20px; font-weight: 600; margin: 0; }
        .category-card p { font-size: 12px; opacity: 0.8; }
        .brand-strip { display: flex; justify-content: center; gap: 40px; align-items: center; padding: 40px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; margin: 40px 0; }
        .brand-strip span { font-size: 16px; font-weight: 700; color: #ccc; text-transform: uppercase; letter-spacing: 2px; }
        .hero-section { position: relative; background: #f5f5f5; padding: 80px 0; text-align: center; }
        .hero-section h1 { font-size: 48px; font-weight: 300; color: #333; margin-bottom: 10px; }
        .hero-section h1 span { font-weight: 700; }
        .hero-section p { color: #666; font-size: 16px; margin-bottom: 30px; }
        .hero-section .btn-shop { display: inline-block; padding: 14px 40px; background: #000; color: #fff; text-transform: uppercase; letter-spacing: 2px; font-size: 13px; text-decoration: none; }
        .hero-section .btn-shop:hover { background: #333; color: #fff; }
        .footer-custom { background: #222; color: #999; padding: 60px 0 30px; }
        .footer-custom h4 { color: #fff; font-size: 16px; font-weight: 600; margin-bottom: 20px; }
        .footer-custom ul { list-style: none; padding: 0; }
        .footer-custom ul li { margin-bottom: 8px; }
        .footer-custom ul li a { color: #999; text-decoration: none; font-size: 14px; }
        .footer-custom ul li a:hover { color: #fff; }
        .navbar-real { background: #fff; padding: 20px 0; border-bottom: 1px solid #eee; }
        .navbar-real .logo { font-size: 24px; font-weight: 700; color: #000; text-decoration: none; }
        .navbar-real .nav-links { display: flex; gap: 30px; align-items: center; }
        .navbar-real .nav-links a { color: #333; text-decoration: none; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; }
        .navbar-real .nav-links a:hover { color: #000; }
    </style>
</head>
<body>
    @if($isPreview ?? false)
        <div class="preview-banner">MODO VISTA PREVIA — Los cambios no han sido guardados</div>
        <div class="preview-spacer"></div>
    @endif

    {{-- Navbar --}}
    <nav class="navbar-real">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="#" class="logo">{{ $siteName }}</a>
                <div class="nav-links">
                    <a href="#">Inicio</a>
                    <a href="#">Tienda</a>
                    <a href="#">Categorias</a>
                    <a href="#">Marcas</a>
                    <a href="#">Nosotros</a>
                </div>
                <div class="nav-links">
                    <a href="#"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></a>
                    <a href="#"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="hero-section">
        <h1>Nueva <span>Coleccion</span></h1>
        <p>Descubre las ultimas tendencias con envio gratuito en compras mayores a $100</p>
        <a href="#" class="btn-shop">Explorar Tienda</a>
    </section>

    {{-- Categories --}}
    <div class="container">
        <h2 class="section-title">Compra por <span>Categoria</span></h2>
        <div class="category-grid">
            @foreach($categories->take(6) as $cat)
                <div class="category-card">
                    <div class="overlay">
                        <h3>{{ $cat->name }}</h3>
                        <p>{{ $cat->products_count }} productos</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Featured Products --}}
    <div class="container">
        <h2 class="section-title">Productos <span>Destacados</span></h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px;">
            @foreach($allProducts->take(8) as $product)
                <div class="real-product-card">
                    <img src="{{ $product->primary_image_url ?: 'https://placehold.co/400x400/f5f5f5/999?text='.urlencode($product->name) }}" alt="{{ $product->name }}">
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
                    </div>
                    <button class="btn-add">Agregar al Carrito</button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brands --}}
    <div class="brand-strip">
        @foreach($brands as $brand)
            <span>{{ $brand->name }}</span>
        @endforeach
    </div>

    {{-- New Arrivals --}}
    @if($newProducts->count() > 0)
        <div class="container">
            <h2 class="section-title">Recien <span>Llegados</span></h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px;">
                @foreach($newProducts->take(4) as $product)
                    <div class="real-product-card">
                        <img src="{{ $product->primary_image_url ?: 'https://placehold.co/400x400/f5f5f5/999?text='.urlencode($product->name) }}" alt="{{ $product->name }}">
                        <div class="info">
                            @if($product->brand)
                                <div class="brand">{{ $product->brand->name }}</div>
                            @endif
                            <div class="name">{{ $product->name }}</div>
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                        </div>
                        <button class="btn-add">Agregar al Carrito</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <footer class="footer-custom">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px;">
                <div>
                    <h4>{{ $siteName }}</h4>
                    <p style="font-size: 14px;">Tu tienda de confianza con los mejores productos y precios.</p>
                </div>
                <div>
                    <h4>Tienda</h4>
                    <ul>
                        @foreach($categories->take(4) as $cat)
                            <li><a href="#">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4>Soporte</h4>
                    <ul>
                        <li><a href="#">Contactanos</a></li>
                        <li><a href="#">Envios</a></li>
                        <li><a href="#">Devoluciones</a></li>
                        <li><a href="#">Preguntas Frecuentes</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Terminos de Servicio</a></li>
                        <li><a href="#">Politica de Privacidad</a></li>
                    </ul>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #444; font-size: 13px;">
                &copy; {{ date('Y') }} {{ $siteName }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</body>
</html>
