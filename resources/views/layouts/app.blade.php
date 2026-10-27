<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#A50046">
    <meta name="unifood-service-worker" content="{{ asset('sw.js') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="UniFood">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
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
        <div class="alert alert-warning" role="status" data-connection-notice hidden>Sin conexión. Necesitas internet para consultar o enviar pedidos. Si estabas enviando uno, revisa Mis pedidos al reconectarte antes de repetirlo.</div>
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
        <div class="pwa-install"><button type="button" class="btn btn-outline-primary btn-sm" data-pwa-install hidden>Instalar UniFood</button><span data-pwa-help hidden>Para instalar UniFood en iPhone o iPad: abre Compartir y elige «Agregar a inicio».</span></div>
        <span class="footer-brand">UniFood<span> · Hecho para tu vida en el campus.</span></span>
        <span><x-icon name="pin" :size="15"/> UNIVALLE · Campus principal <i></i> Pago al recoger</span>
    </footer>
    <div class="app-toast" role="status" aria-live="polite" aria-atomic="true" hidden><x-icon name="check"/><span data-toast-message></span><a href="{{ url('/carrito') }}" data-toast-cart>Ver carrito →</a><button type="button" aria-label="Cerrar notificación" data-toast-close>×</button></div>
    <dialog class="confirmation-dialog" data-confirm-dialog aria-labelledby="confirmation-title" aria-describedby="confirmation-message">
        <div class="confirmation-heading"><span class="confirmation-brand">UniFood</span><button type="button" class="confirmation-close" data-confirm-cancel aria-label="Cerrar confirmación">×</button></div>
        <div class="confirmation-icon" aria-hidden="true">!</div>
        <h2 id="confirmation-title" data-confirm-title></h2>
        <p id="confirmation-message" data-confirm-message></p>
        <div class="confirmation-actions"><button type="button" class="btn btn-outline-secondary" data-confirm-cancel autofocus>Volver</button><button type="button" class="btn btn-primary" data-confirm-accept>Confirmar</button></div>
    </dialog>
</body>
</html>
