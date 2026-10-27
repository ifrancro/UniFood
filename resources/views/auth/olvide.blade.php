@extends('layouts.app')
@section('title', 'Recuperar acceso · UniFood')
@section('content')
<x-auth-shell>
    <span class="eyebrow">VOLVAMOS A TU PAUSA</span><h2>Recupera tu acceso.</h2><p class="auth-intro">Ingresa tu correo para generar un enlace de recuperación.</p>
    <form method="POST" action="{{ url('/password/olvide') }}">@csrf
        <div class="mb-4"><label for="email" class="form-label">Correo institucional</label><input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" autocomplete="email" required autofocus></div>
        <button type="submit" class="btn btn-primary w-100">Generar enlace <x-icon/></button>
    </form><p class="auth-bottom mt-4"><a href="{{ url('/login') }}">← Volver al inicio de sesión</a></p>
</x-auth-shell>
@endsection
