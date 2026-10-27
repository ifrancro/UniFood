@extends('layouts.app')
@section('title', 'Mis pedidos · UniFood')
@section('content')
<div class="page-heading" data-reveal><div><span class="eyebrow">TE ACOMPAÑAMOS HASTA TU PAUSA</span><h1>Mis pedidos<span class="heading-dot">.</span></h1><p>Consulta cómo va tu pedido y pasa por él cuando esté listo.</p></div><a href="{{ url('/catalogo') }}" class="btn btn-primary">Nuevo pedido <x-icon name="plus" :size="18"/></a></div>
<p class="live-note mb-4" data-live-note>Actualización automática · cada 15 s</p>
<div data-live-region data-signature="{{ $pedidos->map(fn($p) => $p->id.':'.$p->estado)->implode('|') }}" aria-live="polite">
@if($pedidos->count() > 0)
    <div class="orders-grid">
    @foreach($pedidos as $pedido)
        <article class="order-card">
            <div class="order-card-head"><div><span class="order-code">{{ $pedido->codigo }}</span><div class="order-date">{{ $pedido->created_at->format('d/m/Y · H:i') }}</div></div><x-status :state="$pedido->estado"/></div>
            <x-order-progress :state="$pedido->estado"/>
            @if($pedido->estado === 'listo')<p class="small mb-2">¡Tu pausa está lista! Pasa por UniFood y paga al recoger.</p>@endif
            <div class="order-card-foot"><strong><small class="text-muted fs-6">Bs.</small> {{ number_format($pedido->total, 2) }}</strong><div class="d-flex gap-2 flex-wrap"><a href="{{ url('/mis-pedidos/'.$pedido->id) }}" class="btn btn-outline-primary btn-sm">Ver detalle <x-icon :size="14"/></a>@if($pedido->estado === 'pendiente')<form action="{{ url('/mis-pedidos/'.$pedido->id.'/cancelar') }}" method="POST" data-confirm-title="¿Cancelar pedido?" data-confirm="Vas a cancelar el pedido {{ $pedido->codigo }}. Esta acción no se puede deshacer." data-confirm-label="Sí, cancelar pedido">@csrf<button type="submit" class="btn btn-sm btn-outline-secondary">Cancelar</button></form>@endif</div></div>
        </article>
    @endforeach
    </div>
@else
    <div class="empty-state"><x-icon name="receipt" :size="44"/><h2>Tu primera pausa te espera</h2><p>Aún no tienes pedidos registrados.<br>Encuentra tu favorito en el menú de UniFood.</p><a href="{{ url('/catalogo') }}" class="btn btn-primary">Explorar el menú <x-icon/></a></div>
@endif
</div>
@endsection
