# Application shell. Single shared Blade layout, role-based navbar and alerts.
## resources/views/layouts/app.blade.php
~~~
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniFood - Tienda Universitaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ Auth::check() ? (Auth::user()->rol === 'estudiante' ? url('/catalogo') : url('/panel/pedidos')) : url('/login') }}">UniFood</a>
            
            <div class="navbar-nav me-auto">
                @auth
                    @if(Auth::user()->rol === 'estudiante')
                        <a class="nav-link" href="{{ url('/catalogo') }}">Catálogo</a>
                        <a class="nav-link" href="{{ url('/carrito') }}">
                            Carrito ({{ array_sum(session('carrito', [])) }})
                        </a>
                        <a class="nav-link" href="{{ url('/mis-pedidos') }}">Mis Pedidos</a>
                    @endif

                    @if(in_array(Auth::user()->rol, ['empleado', 'admin']))
                        <a class="nav-link" href="{{ url('/panel/pedidos') }}">Panel Pedidos</a>
                    @endif

                    @if(Auth::user()->rol === 'admin')
                        <a class="nav-link" href="{{ url('/admin/productos') }}">Gestión Productos</a>
                        <a class="nav-link" href="{{ url('/admin/productos/crear') }}">Nuevo Producto</a>
                    @endif
                @endauth
            </div>

            <div class="navbar-nav ms-auto align-items-center">
                @auth
                    <span class="navbar-text me-3 text-white">
                        <strong>{{ Auth::user()->name }}</strong> 
                        <span class="badge bg-secondary text-uppercase">{{ Auth::user()->rol }}</span>
                    </span>
                    <form action="{{ url('/logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
                    </form>
                @else
                    <a class="nav-link" href="{{ url('/login') }}">Iniciar Sesión</a>
                    <a class="nav-link" href="{{ url('/registro') }}">Registrarse</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>

~~~
