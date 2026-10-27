# Shared primitives. No component directory. Bootstrap 5.3.3 buttons, fields, badges, tables and cards are inline in Blade. Common shell provides alerts.
## resources/views/auth/login.blade.php
~~~
@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Iniciar Sesión - UniFood</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Institucional</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Ingresar</button>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ url('/password/olvide') }}" class="small">¿Olvidaste tu contraseña?</a>
                        <a href="{{ url('/registro') }}" class="small">Crear cuenta de estudiante</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

~~~
