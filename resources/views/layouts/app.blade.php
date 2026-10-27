<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#A50046">
    <meta name="description" content="Tu pausa favorita en UNIVALLE. Pide en UniFood y paga al recoger en el campus.">
    <title>@yield('title', 'UniFood · Campus & café')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/univalle.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ Auth::guest() ? 'guest-page' : 'app-page' }}">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>
    <header class="site-header">
        <nav class="app-container nav-shell" aria-label="Navegación principal">
            <a class="brand" href="{{ Auth::check() ? (Auth::user()->rol === 'estudiante' ? url('/catalogo') : url('/panel/pedidos')) : url('/login') }}">
                <img src="{{ asset('images/univalle.png') }}" width="48" height="48" alt="UNIVALLE">
                <span><strong>Uni<span>Food</span></strong><small>CAMPUS & CAFÉ</small></span>
            </a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav" aria-label="Abrir menú"><x-icon name="menu"/></button>
            <div id="main-nav" class="nav-content">
                <div class="nav-links">
                    @auth
                        @if(Auth::user()->rol === 'estudiante')
                            <a class="{{ request()->is('catalogo') ? 'active' : '' }}" href="{{ url('/catalogo') }}" @if(request()->is('catalogo')) aria-current="page" @endif><x-icon name="grid" :size="18"/> El menú</a>
                            <a class="{{ request()->is('mis-pedidos*') ? 'active' : '' }}" href="{{ url('/mis-pedidos') }}" @if(request()->is('mis-pedidos*')) aria-current="page" @endif><x-icon name="receipt" :size="18"/> Mis pedidos</a>
                        @else
                            <a class="{{ request()->is('panel/pedidos*') ? 'active' : '' }}" href="{{ url('/panel/pedidos') }}"><x-icon name="receipt" :size="18"/> Pedidos</a>
                            @if(Auth::user()->rol === 'admin')
                                <a class="{{ request()->is('admin/productos*') ? 'active' : '' }}" href="{{ url('/admin/productos') }}"><x-icon name="grid" :size="18"/> Productos</a>
                            @endif
                        @endif
                    @else
                        <span class="nav-campus"><x-icon name="pin" :size="16"/> UNIVALLE · Campus principal</span>
                    @endauth
                </div>
                <div class="nav-actions">
                    @auth
                        @if(Auth::user()->rol === 'estudiante')
                            <a class="cart-link {{ request()->is('carrito') ? 'active' : '' }}" href="{{ url('/carrito') }}"><x-icon name="bag"/> Mi carrito <span class="cart-count" data-cart-count>{{ array_sum(session('carrito', [])) }}</span></a>
                        @endif
                        <div class="user-identity"><span class="avatar">{{ mb_substr(Auth::user()->name, 0, 1) }}</span><span><strong>{{ Auth::user()->name }}</strong><small>{{ ucfirst(Auth::user()->rol) }}</small></span></div>
                        <form action="{{ url('/logout') }}" method="POST">@csrf<button type="submit" class="logout-button" aria-label="Cerrar sesión" title="Cerrar sesión"><x-icon name="logout"/></button></form>
                    @else
                        <a href="{{ url('/login') }}" class="nav-login {{ request()->is('login') ? 'active' : '' }}">Ingresar</a>
                        <a href="{{ url('/registro') }}" class="btn btn-primary btn-sm">Crear cuenta <x-icon :size="16"/></a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>
    <main id="main-content" class="app-container main-content">
        <div class="flash-messages">
            @if(session('success'))
                <div class="alert alert-success" role="status" data-success><x-icon name="check"/><span>{{ session('success') }}</span></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" role="alert" data-error>{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert" data-error><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
        </div>
        @yield('content')
    </main>
    <footer class="site-footer app-container">
        <span class="footer-brand">UniFood<span> · Hecho para tu vida en el campus.</span></span>
        <span><x-icon name="pin" :size="15"/> UNIVALLE · Campus principal <i></i> Pago al recoger</span>
    </footer>
    <div class="app-toast" role="status" aria-live="polite" aria-atomic="true" hidden><x-icon name="check"/><span data-toast-message></span><a href="{{ url('/carrito') }}" data-toast-cart>Ver carrito →</a><button type="button" aria-label="Cerrar notificación" data-toast-close>×</button></div>
</body>
</html>
