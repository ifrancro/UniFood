@extends('layouts.app')
@section('title', 'Mi carrito · UniFood')
@section('content')
<div class="page-heading" data-reveal><div><span class="eyebrow">TU PAUSA, A TU GUSTO</span><h1>Mi carrito<span class="heading-dot">.</span></h1><p>Un último vistazo antes de preparar algo rico para ti.</p></div><a href="{{ url('/catalogo') }}" class="btn btn-outline-secondary">← Seguir eligiendo</a></div>
@if(count($items) > 0)
<div class="cart-layout">
    <section class="cart-items" aria-label="Productos del carrito">
        @foreach($items as $item)
            <article class="cart-item" data-reveal>
                <div class="cart-art visual-{{ $item['producto']->categoria }}"><x-product-art :name="$item['producto']->nombre" :category="$item['producto']->categoria"/></div>
                <div class="cart-item-info"><span class="product-category">{{ $item['producto']->categoria }}</span><h3>{{ $item['producto']->nombre }}</h3><p>Bs. {{ number_format($item['producto']->precio, 2) }} por unidad</p>
                    <form action="{{ url('/carrito/actualizar') }}" method="POST" class="cart-quantity">@csrf
                        <input type="hidden" name="producto_id" value="{{ $item['producto']->id }}">
                        <label class="visually-hidden" for="cart-qty-{{ $item['producto']->id }}">Cantidad de {{ $item['producto']->nombre }}</label>
                        <div class="quantity-stepper"><button type="button" data-step="-1" aria-label="Reducir cantidad de {{ $item['producto']->nombre }}"><x-icon name="minus" :size="14"/></button><input id="cart-qty-{{ $item['producto']->id }}" type="number" name="cantidad" min="1" max="50" value="{{ $item['cantidad'] }}" required><button type="button" data-step="1" aria-label="Aumentar cantidad de {{ $item['producto']->nombre }}"><x-icon name="plus" :size="14"/></button></div>
                        <button type="submit" class="text-button">Actualizar</button>
                    </form>
                </div>
                <div class="cart-item-end"><strong>Bs. {{ number_format($item['subtotal'], 2) }}</strong><form action="{{ url('/carrito/eliminar') }}" method="POST">@csrf<input type="hidden" name="producto_id" value="{{ $item['producto']->id }}"><button type="submit" class="remove-button" aria-label="Eliminar {{ $item['producto']->nombre }}">Eliminar</button></form></div>
            </article>
        @endforeach
        <div class="pickup-note"><x-icon name="pin"/><div><strong>Nos vemos en UniFood</strong><p>Recoge tu pedido en la tienda del campus principal cuando esté listo.</p></div></div>
    </section>
    <aside class="order-summary" data-reveal><span class="eyebrow">CASI LISTO</span><h2>Tu pedido</h2><div class="summary-line"><span>Productos ({{ array_sum(array_column($items, 'cantidad')) }})</span><strong>Bs. {{ number_format($total, 2) }}</strong></div><div class="summary-line"><span>Forma de entrega</span><span>Recogida en tienda</span></div><div class="summary-total"><span>Total</span><strong><small>Bs.</small> {{ number_format($total, 2) }}</strong></div><div class="payment-note"><x-icon name="receipt"/><div><strong>Pago al recoger</strong><p>Paga presencialmente en UniFood. No necesitas pagar en línea.</p></div></div><form action="{{ url('/pedidos') }}" method="POST">@csrf<button type="submit" class="btn btn-primary w-100">Confirmar pedido <x-icon/></button></form><p class="summary-caption">Después de confirmar podrás consultar el estado en Mis pedidos.</p></aside>
</div>
@else
<section class="empty-state cart-empty" data-reveal>
    <svg class="empty-cart-art" width="200" height="160" viewBox="0 0 200 160" fill="none" aria-hidden="true"><ellipse cx="100" cy="142" rx="68" ry="10" fill="#E9E3E5"/><g class="basket"><path d="M39 65h122l-14 69H54L39 65Z" fill="#F2DCE5" stroke="#A50046" stroke-width="4" stroke-linejoin="round"/><path d="m62 65 24-35m52 35-24-35M72 84l7 31m21-31v31m28-31-7 31" stroke="#A50046" stroke-width="4" stroke-linecap="round"/></g><path class="basket-spark" d="M164 27v18m-9-9h18M30 31v12m-6-6h12" stroke="#C49348" stroke-width="3" stroke-linecap="round"/></svg>
    <span class="eyebrow">UNA PAUSA EMPIEZA AQUÍ</span><h2>Tu carrito está vacío</h2><p>Hay un café y algo rico esperando por ti.<br>Explora el menú y encuentra tu próximo favorito.</p><a href="{{ url('/catalogo') }}" class="btn btn-primary">Explorar el menú <x-icon/></a>
</section>
@endif
@endsection
