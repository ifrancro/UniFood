@props(['name' => 'arrow', 'size' => 20])
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('bag') <path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/> @break
        @case('coffee') <path d="M3 8h13v6a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8Z"/><path d="M16 8h2a3 3 0 0 1 0 6h-2M2 22h17M7 2v3M12 2v3"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('pin') <path d="M19 10c0 6-7 11-7 11S5 16 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('minus') <path d="M5 12h14"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('logout') <path d="M10 4H4v16h6M14 8l4 4-4 4M9 12h10"/> @break
        @case('grid') <rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/> @break
        @case('receipt') <path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/><path d="M9 7h6M9 11h6M9 15h3"/> @break
        @case('eye') <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
        @case('leaf') <path d="M20 3C9 2 3 6 5 14s15 4 15-11Z"/><path d="m3 21 12-12"/> @break
        @default <path d="M4 12h16m-6-6 6 6-6 6"/>
    @endswitch
</svg>
