@extends('layouts.app')
@section('title', 'Nueva contraseña · UniFood')
@section('content')
<x-auth-shell>
    <span class="eyebrow">UN NUEVO COMIENZO</span><h2>Tu nueva contraseña.</h2><p class="auth-intro">Elige una contraseña para volver a ingresar a UniFood.</p>
    <form method="POST" action="{{ url('/password/reset') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3"><label for="email" class="form-label">Correo institucional</label><input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" autocomplete="username" required></div>
        <div class="mb-3"><label for="password" class="form-label">Nueva contraseña</label><div class="password-field"><input type="password" id="password" name="password" class="form-control" autocomplete="new-password" minlength="6" required><button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><x-icon name="eye" :size="18"/></button></div></div>
        <div class="mb-4"><label for="password_confirmation" class="form-label">Confirmar contraseña</label><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="6" required></div>
        <button type="submit" class="btn btn-primary w-100">Guardar contraseña <x-icon/></button>
    </form><p class="auth-bottom mt-4"><a href="{{ url('/login') }}">← Volver al inicio de sesión</a></p>
</x-auth-shell>
@endsection
