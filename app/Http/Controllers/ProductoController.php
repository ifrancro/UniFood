<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        // Incluye inactivos
        $productos = Producto::orderBy('id', 'desc')->get();

        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $producto = new Producto([
            'disponible' => true,
            'activo' => true,
        ]);

        return view('admin.productos.form', compact('producto'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:productos,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0.01'],
            'categoria' => ['required', 'string', 'in:bebidas,comidas,snacks'],
        ]);

        Producto::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'precio' => $validated['precio'],
            'categoria' => $validated['categoria'],
            'disponible' => $request->boolean('disponible'),
            'activo' => true,
        ]);

        return redirect('/admin/productos')->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Producto $producto)
    {
        return view('admin.productos.form', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:productos,nombre,' . $producto->id],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0.01'],
            'categoria' => ['required', 'string', 'in:bebidas,comidas,snacks'],
        ]);

        $producto->nombre = $validated['nombre'];
        $producto->descripcion = $validated['descripcion'];
        $producto->precio = $validated['precio'];
        $producto->categoria = $validated['categoria'];
        $producto->disponible = $request->boolean('disponible');
        
        $producto->activo = $request->boolean('activo');

        $producto->save();

        return redirect('/admin/productos')->with('success', 'Producto actualizado correctamente.');
    }

    public function toggleDisponibilidad(Producto $producto)
    {
        $producto->disponible = !$producto->disponible;
        $producto->save();

        $estado = $producto->disponible ? 'disponible' : 'agotado';

        return back()->with('success', "El producto '{$producto->nombre}' ahora está {$estado}.");
    }

    public function desactivar(Producto $producto)
    {
        // Baja lógica: activo = false. Nunca delete().
        $producto->activo = false;
        $producto->save();

        return back()->with('success', "El producto '{$producto->nombre}' ha sido desactivado (baja lógica).");
    }
}
