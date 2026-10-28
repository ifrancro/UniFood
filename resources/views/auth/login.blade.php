@extends('layouts.app')
@section('title', 'Ingresar · UniFood')
@section('content')
<x-auth-shell>
    <span class="eyebrow">BIENVENIDO A UNIFOOD</span>
    <h2>Tu próxima pausa<br>empieza aquí.</h2>
    <p class="auth-intro">Ingresa a tu cuenta y encuentra algo rico para hoy.</p>
    @if(session('lockout_seconds'))
        <div class="alert alert-warning mb-3 d-flex align-items-center gap-2" role="alert" data-lockout-timer data-seconds="{{ session('lockout_seconds') }}">
            <x-icon name="clock" :size="20"/>
            <div>
                <strong>Acceso temporalmente bloqueado</strong>
                <p class="mb-0 small">Demasiados intentos fallidos. Podrás intentar de nuevo en <span data-seconds-display>{{ session('lockout_seconds') }}</span> segundos.</p>
            </div>
        </div>
    @endif
    <form method="POST" action="{{ url('/login') }}">@csrf
        <div class="mb-3"><label for="email" class="form-label">Correo institucional</label><input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="tu.correo@univalle.edu" autocomplete="username" required autofocus></div>
        <div class="mb-3"><label for="password" class="form-label">Contraseña</label><div class="password-field"><input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><x-icon name="eye" :size="18"/></button></div></div>
        <div class="text-end mb-4"><a href="{{ url('/password/olvide') }}" class="small">¿Olvidaste tu contraseña?</a></div>
        <button type="submit" class="btn btn-primary w-100" @if(session('lockout_seconds')) disabled @endif>Ingresar <x-icon/></button>
    </form>
    <div class="auth-divider">TU PAUSA EN EL CAMPUS</div>
    <p class="auth-bottom">¿Primera vez por aquí? <a href="{{ url('/registro') }}">Crea tu cuenta</a></p>
</x-auth-shell>
@endsection
