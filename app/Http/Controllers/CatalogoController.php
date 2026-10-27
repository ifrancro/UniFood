<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::where('activo', true);

        if ($request->filled('categoria') && in_array($request->categoria, ['bebidas', 'comidas', 'snacks'])) {
            $query->where('categoria', $request->categoria);
        }

        $productos = $query->orderBy('categoria')->orderBy('nombre')->get();
        $categoriaSeleccionada = $request->query('categoria', '');

        return view('catalogo.index', [
            'productos' => $productos,
            'categoriaSeleccionada' => $categoriaSeleccionada,
        ]);
    }
}
