@extends('layouts.app')

@section('content')
<div class="page-heading" data-reveal>
    <div><span class="eyebrow">ADMINISTRACIÓN · UNIFOOD</span><h1>El menú, siempre al día<span class="heading-dot">.</span></h1><p>Gestiona los precios y la disponibilidad de tu tienda.</p></div>
    <a href="{{ url('/admin/productos/crear') }}" class="btn btn-success">+ Nuevo Producto</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio (Bs.)</th>
                        <th>Disponibilidad</th>
                        <th>En catálogo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr class="{{ !$producto->activo ? 'table-secondary text-muted' : '' }}">
                            <td>{{ $producto->id }}</td>
                            <td>
                                <div class="inventory-product"><div class="inventory-art"><x-product-art :name="$producto->nombre" :category="$producto->categoria"/></div><strong>{{ $producto->nombre }}</strong></div>
                                @if($producto->descripcion)
                                    <br><small class="text-muted">{{ $producto->descripcion }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ $producto->categoria }}</span>
                            </td>
                            <td class="fw-bold">{{ number_format($producto->precio, 2) }} Bs.</td>
                            <td>
                                @if($producto->disponible)
                                    <span class="badge bg-success">Disponible</span>
                                @else
                                    <span class="badge bg-danger">Agotado</span>
                                @endif
                            </td>
                            <td>
                                @if($producto->activo)
                                    <span class="badge bg-primary">Activo</span>
                                @else
                                    <span class="badge bg-dark">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="inventory-actions">
                                    <a href="{{ url('/admin/productos/' . $producto->id . '/editar') }}" class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>

                                    <form action="{{ url('/admin/productos/' . $producto->id . '/disponibilidad') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $producto->disponible ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                            {{ $producto->disponible ? 'Marcar Agotado' : 'Marcar Disponible' }}
                                        </button>
                                    </form>

                                    @if($producto->activo)
                                        <form action="{{ url('/admin/productos/' . $producto->id . '/desactivar') }}" method="POST" data-confirm-title="¿Desactivar producto?" data-confirm="El producto «{{ $producto->nombre }}» dejará de aparecer en el catálogo de estudiantes. Podrás volver a activarlo desde Editar." data-confirm-label="Sí, desactivar">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Desactivar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay productos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

