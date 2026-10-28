@extends('layouts.app')
@section('title', 'Pedidos de la tienda · UniFood')
@section('content')
<div class="page-heading" data-reveal><div><span class="eyebrow">UNIFOOD · OPERACIÓN DE LA TIENDA</span><h1>Cada pedido, una pausa<span class="heading-dot">.</span></h1><p>Organiza la preparación y avisa cuando esté listo para recoger.</p></div><span class="live-note" data-live-note>Actualización automática · cada 15 s</span></div>
<nav class="category-tabs" aria-label="Filtrar pedidos por estado">
@foreach(['' => 'Todos', 'pendiente' => 'Pendientes', 'en_preparacion' => 'En preparación', 'listo' => 'Listos', 'entregado' => 'Entregados', 'cancelado' => 'Cancelados'] as $state => $label)
<a href="{{ url('/panel/pedidos').($state ? '?estado='.$state : '') }}" class="{{ $estadoSeleccionado === $state ? 'active' : '' }}" @if($estadoSeleccionado === $state) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav>
<div data-live-region data-signature="{{ $pedidos->map(fn($p) => $p->id.':'.$p->estado)->implode('|') }}" aria-live="polite">
    <div class="stats-grid">
        @foreach(['pendiente' => 'Pendientes', 'en_preparacion' => 'En preparación', 'listo' => 'Listos para recoger', 'entregado' => 'Entregados'] as $state => $label)
        <div class="stat-card"><div><small>{{ $label }}</small><strong>{{ $pedidos->where('estado', $state)->count() }}</strong></div><span class="stat-icon"><x-icon :name="match($state) { 'pendiente' => 'receipt', 'en_preparacion' => 'coffee', 'listo' => 'bag', default => 'check' }"/></span></div>
        @endforeach
    </div>
    <p class="small text-muted mb-3">{{ $pedidos->count() }} pedidos en esta vista · Los pendientes aparecen primero</p>
    <div class="staff-grid">
        @forelse($pedidos as $pedido)
        <article class="order-card staff-order">
            <div class="order-card-head"><span class="order-code">{{ $pedido->codigo }}</span><x-status :state="$pedido->estado"/></div>
            <div class="order-date">{{ $pedido->created_at->format('d/m/Y · H:i') }}</div>
            <h3>{{ $pedido->user->name }}</h3><p class="customer-email">{{ $pedido->user->email }}</p>
            @foreach($pedido->detalles as $detalle)<div class="order-line"><b>{{ $detalle->cantidad }}×</b><span>{{ $detalle->nombre_producto }}</span></div>@endforeach
            <div class="order-card-foot"><strong>Bs. {{ number_format($pedido->total, 2) }}</strong><a href="{{ url('/panel/pedidos/'.$pedido->id) }}" class="small">Ver detalle →</a></div>
            @if(isset(\App\Models\Pedido::TRANSICIONES[$pedido->estado]))
                <form action="{{ url('/panel/pedidos/'.$pedido->id.'/estado') }}" method="POST">@csrf<input type="hidden" name="estado_esperado" value="{{ $pedido->estado }}"><button type="submit" class="btn btn-primary">{{ \App\Models\Pedido::TEXTO_SIGUIENTE_ESTADO[$pedido->estado] }} <x-icon :size="16"/></button></form>
            @else
                <p class="mb-0 small">{{ $pedido->estado === 'entregado' ? '✓ Pedido entregado' : 'Pedido cancelado' }}</p>
            @endif
        </article>
        @empty
        <div class="empty-state"><x-icon name="receipt" :size="42"/><h3>Todo al día por aquí</h3><p>No hay pedidos en esta vista.</p><a href="{{ url('/panel/pedidos') }}" class="btn btn-outline-secondary">Ver todos los pedidos</a></div>
        @endforelse
    </div>
</div>
@endsection
