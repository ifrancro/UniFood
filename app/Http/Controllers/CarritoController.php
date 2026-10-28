<?php

namespace App\Http\Controllers;

use App\Importe;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CarritoController extends Controller
{
    public function index()
    {
        $carrito = session('carrito', []);
        $items = [];
        $totalCentavos = 0;

        if (! empty($carrito)) {
            $productos = Producto::whereIn('id', array_keys($carrito))->get()->keyBy('id');

            foreach ($carrito as $productoId => $cantidad) {
                if ($producto = $productos->get($productoId)) {
                    $subtotalCentavos = Importe::aCentavos($producto->precio) * (int) $cantidad;
                    $totalCentavos += $subtotalCentavos;

                    $items[] = [
                        'producto' => $producto,
                        'cantidad' => (int) $cantidad,
                        'subtotal' => Importe::aDecimal($subtotalCentavos),
                    ];
                }
            }
        }

        $orderToken = session('order_token');
        if (! $orderToken) {
            $orderToken = (string) Str::uuid();
            session(['order_token' => $orderToken]);
        }

        return view('carrito.index', [
            'items' => $items,
            'total' => Importe::aDecimal($totalCentavos),
            'orderToken' => $orderToken,
        ]);
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        if (! $producto->activo || ! $producto->disponible) {
            return back()->with('error', 'El producto seleccionado no está disponible para la compra.');
        }

        $carrito = session('carrito', []);
        $id = (int) $request->producto_id;
        $cantidad = (int) $request->cantidad;

        $nuevaCantidad = ($carrito[$id] ?? 0) + $cantidad;
        if ($nuevaCantidad > 50) {
            throw ValidationException::withMessages(['cantidad' => 'Puedes pedir como máximo 50 unidades de un producto.']);
        }

        $carrito[$id] = $nuevaCantidad;
        session(['carrito' => $carrito]);

        return redirect('/carrito')->with('success', 'Producto añadido al carrito.');
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'producto_id' => ['required'],
            'cantidad' => ['required', 'integer', 'min:0', 'max:50'],
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
