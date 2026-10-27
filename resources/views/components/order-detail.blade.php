@props(['pedido'])
@php($isStaff = Auth::user()->rol !== 'estudiante')
<div class="detail-layout">
    <a href="{{ $isStaff ? url('/panel/pedidos') : url('/mis-pedidos') }}" class="btn btn-outline-secondary btn-sm">← Volver a {{ $isStaff ? 'pedidos de la tienda' : 'mis pedidos' }}</a>
    <p class="live-note mt-3 mb-0" data-live-note>Actualización automática · cada 15 s</p>
    <div data-live-region data-signature="{{ $pedido->id.':'.$pedido->estado }}" aria-live="polite">
        <div class="detail-hero"><div><span class="eyebrow">DETALLE DEL PEDIDO</span><h1>{{ $pedido->codigo }}</h1><x-status :state="$pedido->estado"/><p class="mt-3 mb-0">{{ $pedido->created_at->format('d/m/Y · H:i') }} · {{ $pedido->user->name }}</p>@if($isStaff)<p class="mb-0">{{ $pedido->user->email }}</p>@endif</div><div class="detail-total"><strong>Bs. {{ number_format($pedido->total, 2) }}</strong><p>Total del pedido · Pago al recoger</p></div></div>
        <div class="detail-card"><x-order-progress :state="$pedido->estado"/>
            @if($pedido->estado === 'listo')<div class="alert alert-success">¡Tu pedido está listo para recoger en UniFood!</div>@endif
            <h2 class="mt-3">{{ $isStaff ? 'Productos para preparar' : 'Tu selección' }}</h2>
            @foreach($pedido->detalles as $detalle)
                <div class="detail-product"><div><h3>{{ $detalle->cantidad }}× {{ $detalle->nombre_producto }}</h3><p>Bs. {{ number_format($detalle->precio_unitario, 2) }} por unidad</p></div><strong>Bs. {{ number_format($detalle->subtotal, 2) }}</strong></div>
            @endforeach
        </div>
        <div class="detail-bottom"><div class="payment-note"><x-icon name="pin"/><div><strong>Recogida en UniFood · Campus principal</strong><p>{{ $pedido->estado === 'cancelado' ? 'Este pedido fue cancelado y no será preparado.' : ($pedido->estado === 'entregado' ? 'Gracias por compartir tu pausa con UniFood.' : 'Espera a que el pedido esté listo. Presenta tu código y paga al recoger.') }}</p></div></div>
            @if($isStaff && isset(\App\Models\Pedido::TRANSICIONES[$pedido->estado]))
                <form action="{{ url('/panel/pedidos/'.$pedido->id.'/estado') }}" method="POST">@csrf<button type="submit" class="btn btn-primary">{{ \App\Models\Pedido::TEXTO_SIGUIENTE_ESTADO[$pedido->estado] }} <x-icon/></button></form>
            @endif
        </div>
    </div>
</div>
