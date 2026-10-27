@props(['state'])
@php
    $label = match ($state) { 'pendiente' => 'Pendiente', 'en_preparacion' => 'En preparación', 'listo' => 'Listo para recoger', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado', default => $state };
@endphp
<span class="status-pill status-{{ $state }}"><span class="status-dot"></span>{{ $label }}</span>
