@extends('layouts.app')

@section('content')
<div class="page-heading" data-reveal><div><span class="eyebrow">ADMINISTRACIÓN · UNIFOOD</span><h1>{{ $producto->exists ? 'Actualiza tu menú.' : 'Algo nuevo para el campus.' }}</h1><p>Información clara para que cada estudiante encuentre su favorito.</p></div></div>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $producto->exists ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h4>
                <a href="{{ url('/admin/productos') }}" class="btn btn-outline-light btn-sm">&larr; Volver</a>
            </div>
            <div class="card-body">
                <form action="{{ $producto->exists ? url('/admin/productos/' . $producto->id) : url('/admin/productos') }}" method="POST">
                    @csrf
                    @if($producto->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="255">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="precio" class="form-label">Precio (Bs.)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="precio" name="precio" value="{{ old('precio', $producto->precio) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="" disabled {{ !old('categoria', $producto->categoria) ? 'selected' : '' }}>Seleccionar categoría...</option>
                                <option value="bebidas" {{ old('categoria', $producto->categoria) === 'bebidas' ? 'selected' : '' }}>Bebidas</option>
                                <option value="comidas" {{ old('categoria', $producto->categoria) === 'comidas' ? 'selected' : '' }}>Comidas</option>
                                <option value="snacks" {{ old('categoria', $producto->categoria) === 'snacks' ? 'selected' : '' }}>Snacks</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="hidden" name="disponible" value="0">
                        <input type="checkbox" class="form-check-input" id="disponible" name="disponible" value="1" {{ old('disponible', $producto->disponible ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="disponible">Producto disponible para la venta inmediata</label>
                    </div>

                    @if($producto->exists)
                        <div class="mb-3 form-check">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" {{ old('activo', $producto->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Producto activo en el catálogo (desmarcar para baja lógica)</label>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ url('/admin/productos') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">
                            {{ $producto->exists ? 'Guardar Cambios' : 'Crear Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

