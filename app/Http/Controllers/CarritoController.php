<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index()
    {
        $carrito = session('carrito', []);
        $items = [];
        $total = 0.0;

        if (!empty($carrito)) {
            $productos = Producto::whereIn('id', array_keys($carrito))->get()->keyBy('id');

            foreach ($carrito as $productoId => $cantidad) {
                if ($producto = $productos->get($productoId)) {
                    $subtotal = (float) $producto->precio * (int) $cantidad;
                    $total += $subtotal;

                    $items[] = [
                        'producto' => $producto,
                        'cantidad' => (int) $cantidad,
                        'subtotal' => $subtotal,
                    ];
                }
            }
        }

        $orderToken = session('order_token');
        if (!$orderToken) {
            $orderToken = (string) \Illuminate\Support\Str::uuid();
            session(['order_token' => $orderToken]);
        }

        return view('carrito.index', [
            'items' => $items,
            'total' => $total,
            'orderToken' => $orderToken,
        ]);
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        if (!$producto->activo || !$producto->disponible) {
            return back()->with('error', 'El producto seleccionado no está disponible para la compra.');
        }

        $carrito = session('carrito', []);
        $id = (int) $request->producto_id;
        $cantidad = (int) $request->cantidad;

        $carrito[$id] = ($carrito[$id] ?? 0) + $cantidad;
        session(['carrito' => $carrito]);

        return redirect('/carrito')->with('success', 'Producto añadido al carrito.');
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'producto_id' => ['required'],
            'cantidad' => ['required', 'integer', 'min:0'],
        ]);

        $carrito = session('carrito', []);
        $id = (int) $request->producto_id;
        $cantidad = (int) $request->cantidad;

        if ($cantidad <= 0) {
            unset($carrito[$id]);
        } else {
            $carrito[$id] = $cantidad;
        }

        session(['carrito' => $carrito]);

        return redirect('/carrito')->with('success', 'Carrito actualizado correctamente.');
    }

    public function eliminar(Request $request)
    {
        $request->validate([
            'producto_id' => ['required'],
        ]);

        $carrito = session('carrito', []);
        $id = (int) $request->producto_id;

        unset($carrito[$id]);
        session(['carrito' => $carrito]);

        return redirect('/carrito')->with('success', 'Producto retirado del carrito.');
    }
}
