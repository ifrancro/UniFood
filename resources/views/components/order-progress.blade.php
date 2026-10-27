@props(['state'])
@php
    $steps = ['pendiente' => 'Pendiente', 'en_preparacion' => 'En preparación', 'listo' => 'Listo para recoger', 'entregado' => 'Entregado'];
    $current = array_search($state, array_keys($steps), true);
@endphp
@if($state !== 'cancelado')
    <ol class="order-progress" aria-label="Progreso del pedido">
        @foreach($steps as $key => $label)
            <li class="{{ $current !== false && $loop->index <= $current ? 'is-complete' : '' }}" @if($key === $state) aria-current="step" @endif>
                <span class="step-circle">@if($current !== false && $loop->index < $current)<x-icon name="check" :size="16"/>@else{{ $loop->iteration }}@endif</span>
                <span>{{ $label }}</span>
            </li>
        @endforeach
    </ol>
@endif
