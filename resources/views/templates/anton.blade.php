<x-layouts.app :title="$siteName . ' — Moda & Estilo'">
@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif !important; }
        .an-hero { position: relative; background: #f5f5f5; padding: 80px 0; text-align: center; }
        .an-hero h1 { font-size: 48px; font-weight: 300; color: #333; margin-bottom: 10px; }
        .an-hero h1 span { font-weight: 700; }
        .an-hero p { color: #666; font-size: 16px; margin-bottom: 30px; }
        .an-hero .btn-shop { display: inline-block; padding: 14px 40px; background: #000; color: #fff; text-transform: uppercase; letter-spacing: 2px; font-size: 13px; text-decoration: none; transition: all 0.3s; }
        .an-hero .btn-shop:hover { background: #333; }
        .an-section-title { text-align: center; font-size: 28px; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; margin: 50px 0 30px; color: #333; }
        .an-section-title span { font-weight: 600; }
        .an-product { background: #fff; border: 1px solid #eee; transition: all 0.3s; overflow: hidden; }
        .an-product:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); transform: translateY(-4px); }
        .an-product img { width: 100%; height: 280px; object-fit: cover; }
        .an-product .info { padding: 16px; }
        .an-product .brand { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 4px; }
        .an-product .name { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px; line-height: 1.3; }
        .an-product .name a { color: inherit; text-decoration: none; }
        .an-product .price { font-size: 18px; font-weight: 700; color: #000; }
        .an-product .old-price { font-size: 13px; color: #999; text-decoration: line-through; margin-left: 8px; }
        .an-product .btn-add { display: block; width: 100%; padding: 10px; background: #000; color: #fff; text-align: center; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; border: none; cursor: pointer; text-decoration: none; }
        .an-product .btn-add:hover { background: #333; color: #fff; }
        .an-category-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 60px; }
        .an-cat-card { position: relative; height: 200px; overflow: hidden; background: #f5f5f5; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s; }
        .an-cat-card:hover { background: #eee; }
        .an-cat-card h3 { font-size: 20px; font-weight: 600; color: #333; margin: 0 0 4px; }
        .an-cat-card p { font-size: 12px; color: #999; margin: 0; }
        .an-brand-strip { display: flex; justify-content: center; gap: 40px; align-items: center; padding: 40px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; margin: 40px 0; flex-wrap: wrap; }
        .an-brand-strip a { font-size: 16px; font-weight: 700; color: #ccc; text-transform: uppercase; letter-spacing: 2px; text-decoration: none; transition: color 0.3s; }
        .an-brand-strip a:hover { color: #333; }
    </style>
@endpush

    {{-- Hero --}}
    <section class="an-hero">
        <h1>Nueva <span>Coleccion</span></h1>
        <p>Descubre las ultimas tendencias con envio gratuito en compras mayores a $100</p>
        <a href="{{ route('shop.index') }}" class="btn-shop">Explorar Tienda</a>
    </section>

    {{-- Categories --}}
    <div class="container">
        <h2 class="an-section-title">Compra por <span>Categoria</span></h2>
        <div class="an-category-grid">
            @foreach($categories->take(6) as $cat)
                <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="an-cat-card">
                    <div class="text-center">
                        <h3>{{ $cat->name }}</h3>
                        <p>{{ $cat->products_count }} productos</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Products --}}
    <div class="container">
        <h2 class="an-section-title">Productos <span>Destacados</span></h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px;">
            @foreach($allProducts->take(8) as $product)
                <div class="an-product">
                    <a href="{{ route('products.show', $product) }}">
                        <img src="{{ $product->primary_image_url ?: 'https://placehold.co/400x400/f5f5f5/999?text='.urlencode($product->name) }}" alt="{{ $product->name }}">
                    </a>
                    <div class="info">
                        @if($product->brand)
                            <div class="brand">{{ $product->brand->name }}</div>
                        @endif
                        <div class="name"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></div>
                        <div>
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                            @if($product->compare_price)
                                <span class="old-price">${{ number_format($product->compare_price, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="btn-add">Ver Producto</a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brands --}}
    <div class="an-brand-strip">
        @foreach($brands as $brand)
            <a href="{{ route('shop.index', ['brand' => $brand->slug]) }}">{{ $brand->name }}</a>
        @endforeach
    </div>

</x-layouts.app>
