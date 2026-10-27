@props(['name' => '', 'category' => 'comidas'])
@php
    $image = match (mb_strtolower($name)) {
        'café americano' => 'americano',
        'capuchino' => 'capuchino',
        'jugo natural' => 'jugo',
        'agua mineral' => 'agua',
        'salteña' => 'saltena',
        'empanada' => 'empanada',
        'sándwich de pollo' => 'sandwich',
        'hamburguesa simple' => 'hamburguesa',
        'mandarina' => 'mandarina',
        default => 'generic',
    };
@endphp
<img src="{{ asset('images/menu/'.$image.'.svg') }}" alt="Ilustración de {{ $name ?: $category }}" width="340" height="280" loading="lazy" {{ $attributes->merge(['class' => 'product-illustration']) }}>
