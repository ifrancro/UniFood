@extends('layouts.app')
@section('title', 'El menú · UniFood')
@section('content')
<section class="catalog-hero" data-reveal>
    <div class="hero-copy">
        <span class="eyebrow"><span class="tiny-dot"></span> TU TIENDA EN UNIVALLE</span>
        <h1>Tu pausa favorita,<br><em>sin hacer fila.</em></h1>
        <p>Un café entre clases. Algo rico para continuar.<br>Haz tu pedido y pasa a recogerlo en el campus.</p>
        <a href="#menu" class="btn btn-primary">¿Qué se te antoja? <x-icon/></a>
        <div class="hero-caption"><x-icon name="bag" :size="16"/> Pide desde donde estés <span>·</span> Paga al recoger</div>
    </div>
    <x-food-scene/>
</section>
<div class="service-strip" data-reveal>
    <span><x-icon name="coffee"/> Preparado para tu pausa</span>
    <span><x-icon name="pin"/> Recogida en el campus principal</span>
    <span><x-icon name="receipt"/> Pago presencial, sin complicaciones</span>
</div>
<section id="menu" class="menu-section" aria-labelledby="menu-title">
    <div class="section-heading" data-reveal>
        <div><span class="eyebrow">ALGO RICO TE ESPERA</span><h2 id="menu-title">El menú del campus<span class="heading-dot">.</span></h2><p>Elige tu favorito. Nosotros nos encargamos del resto.</p></div>
        <label class="search-field"><x-icon name="search"/><span class="visually-hidden">Buscar en esta categoría</span><input type="search" placeholder="Buscar algo rico…" data-product-search autocomplete="off"></label>
    </div>
    <nav class="category-tabs" aria-label="Categorías de productos" data-reveal>
        @foreach(['' => 'Todo el menú', 'comidas' => 'Comidas', 'bebidas' => 'Bebidas', 'snacks' => 'Snacks'] as $value => $label)
            <a href="{{ url('/catalogo').($value ? '?categoria='.$value : '') }}" class="{{ $categoriaSeleccionada === $value ? 'active' : '' }}" @if($categoriaSeleccionada === $value) aria-current="page" @endif><x-icon :name="match($value) { 'bebidas' => 'coffee', 'snacks' => 'leaf', 'comidas' => 'bag', default => 'grid' }" :size="18"/>{{ $label }}</a>
        @endforeach
        <span class="menu-count">{{ $productos->count() }} opciones en esta categoría</span>
    </nav>
    <div class="product-grid">
        @forelse($productos as $producto)
            <article class="product-card {{ !$producto->disponible ? 'sold-out' : '' }}" data-product-name="{{ $producto->nombre }}" data-reveal>
                <div class="product-visual visual-{{ $producto->categoria }}">
                    <span class="availability {{ !$producto->disponible ? 'unavailable' : '' }}"><span></span>{{ $producto->disponible ? 'Disponible' : 'Agotado' }}</span>
                    <x-product-art :name="$producto->nombre" :category="$producto->categoria"/>
                </div>
                <div class="product-body">
                    <span class="product-category">{{ $producto->categoria }}</span>
                    <h3>{{ $producto->nombre }}</h3>
                    <p>{{ $producto->descripcion ?: 'Una buena compañía para tu pausa en el campus.' }}</p>
                    <div class="product-purchase">
                        <span class="product-price"><small>Bs.</small> {{ number_format($producto->precio, 2) }}</span>
                        @if($producto->disponible)
                            <form action="{{ url('/carrito/agregar') }}" method="POST" class="add-form" data-add-form>
                                @csrf
                                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                <label class="visually-hidden" for="cantidad-{{ $producto->id }}">Cantidad de {{ $producto->nombre }}</label>
                                <input id="cantidad-{{ $producto->id }}" type="number" name="cantidad" value="1" min="1" max="50" required class="quantity-input">
                                <button type="submit" class="add-button" aria-label="Agregar {{ $producto->nombre }} al carrito"><x-icon name="plus" :size="18"/><span>Agregar</span></button>
                            </form>
                        @else
                            <button class="add-button" disabled>Agotado</button>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state"><x-icon name="coffee" :size="42"/><h3>Aún no hay opciones en esta categoría</h3><p>Descubre los productos del resto del menú.</p><a href="{{ url('/catalogo') }}" class="btn btn-primary">Ver todo el menú</a></div>
        @endforelse
    </div>
    <div class="empty-state search-empty" data-search-empty hidden><x-icon name="search" :size="32"/><h3>No encontramos ese antojo</h3><p>Prueba otro nombre dentro de esta categoría.</p></div>
    <p class="illustration-caption">Las ilustraciones son referenciales. La disponibilidad se confirma al registrar tu pedido.</p>
</section>
@endsection
