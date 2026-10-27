@extends('layouts.app')
@section('title', 'Crear cuenta · UniFood')
@section('content')
<x-auth-shell>
    <span class="eyebrow">HAZ ESPACIO PARA TU PAUSA</span><h2>Crea tu cuenta.</h2><p class="auth-intro">Únete a UniFood y pide antes de llegar a la tienda.</p>
    <form method="POST" action="{{ url('/registro') }}">@csrf
        <div class="mb-3"><label for="name" class="form-label">Nombre completo</label><input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" autocomplete="name" placeholder="¿Cómo te llamas?" required autofocus></div>
        <div class="mb-3"><label for="email" class="form-label">Correo institucional</label><input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" autocomplete="username" placeholder="tu.correo@univalle.edu" required></div>
        <div class="mb-3"><label for="password" class="form-label">Contraseña</label><div class="password-field"><input type="password" id="password" name="password" class="form-control" autocomplete="new-password" minlength="6" placeholder="Al menos 6 caracteres" required><button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false"><x-icon name="eye" :size="18"/></button></div></div>
        <div class="mb-4"><label for="password_confirmation" class="form-label">Confirmar contraseña</label><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="6" required></div>
        <button type="submit" class="btn btn-primary w-100">Crear cuenta <x-icon/></button>
    </form><p class="auth-bottom mt-4">¿Ya tienes cuenta? <a href="{{ url('/login') }}">Ingresa aquí</a></p>
</x-auth-shell>
@endsection
