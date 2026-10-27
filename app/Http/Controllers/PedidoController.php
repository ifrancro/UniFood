<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function store(Request $request)
    {
        return DB::transaction(function () {
            // 1. Leer el carrito de sesión. Si está vacío -> back con error (RN-02).
            $carrito = session('carrito', []);

            if (empty($carrito)) {
                return redirect('/carrito')->with('error', 'El carrito está vacío. Agrega al menos un producto.');
            }

            // 2. Cargar productos por id desde BD.
            $productoIds = array_keys($carrito);
            $productos = Producto::whereIn('id', $productoIds)->lockForUpdate()->get()->keyBy('id');

            $invalidoNombre = null;
            foreach ($carrito as $pId => $cantidad) {
                $p = $productos->get($pId);
                if (!$p || !$p->activo || !$p->disponible) {
                    $invalidoNombre = $p ? $p->nombre : "Producto #{$pId}";
                    unset($carrito[$pId]);
                }
            }

            if ($invalidoNombre) {
                session(['carrito' => $carrito]);
                return redirect('/carrito')->with('error', "El producto '{$invalidoNombre}' ya no se encuentra disponible. Se retiró de tu carrito.");
            }

            // 3. Calcular subtotal = precio_bd * cantidad, y total = suma de subtotales (RN-03).
            $total = 0.0;
            $detalles = [];

            foreach ($carrito as $pId => $cantidad) {
                $qty = (int) $cantidad;
                if ($qty <= 0) {
                    continue;
                }

                $producto = $productos->get($pId);
                $precioUnitario = (float) $producto->precio;
                $subtotal = $precioUnitario * $qty;
                $total += $subtotal;

                $detalles[] = [
                    'producto_id' => $producto->id,
                    'nombre_producto' => $producto->nombre,
                    'precio_unitario' => $precioUnitario,
                    'cantidad' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($detalles)) {
                return redirect('/carrito')->with('error', 'El carrito no contiene productos válidos.');
            }

            // 4. Generar codigo: 'UF-' . date('Ymd') . '-' . str_pad(<correlativo del día>, 4, '0', STR_PAD_LEFT).
            $prefix = 'UF-' . date('Ymd') . '-';
            $countToday = Pedido::where('codigo', 'like', $prefix . '%')->count();
            $correlativo = $countToday + 1;
            $codigo = $prefix . str_pad((string) $correlativo, 4, '0', STR_PAD_LEFT);

            while (Pedido::where('codigo', $codigo)->exists()) {
                $correlativo++;
                $codigo = $prefix . str_pad((string) $correlativo, 4, '0', STR_PAD_LEFT);
            }

            // 5. Crear el Pedido (estado 'pendiente') y sus DetallePedido copiando nombre y precio.
            $pedido = Pedido::create([
                'codigo' => $codigo,
                'user_id' => Auth::id(),
                'estado' => 'pendiente',
                'total' => $total,
            ]);

            foreach ($detalles as $detalle) {
                $pedido->detalles()->create($detalle);
            }

            // 6. Vaciar el carrito y redirigir a /mis-pedidos con el código visible.
            session()->forget('carrito');

            return redirect('/mis-pedidos')->with('success', "¡Pedido registrado con éxito! Tu código de pedido es: {$codigo}");
        });
    }

    public function index()
    {
        // RN-05: El estudiante solo ve SUS pedidos
        $pedidos = Pedido::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    public function show(Pedido $pedido)
    {
        // RN-05: Si un estudiante pide el detalle de un pedido ajeno -> abort(403).
        if ($pedido->user_id !== Auth::id()) {
            abort(403, 'Acceso denegado a este pedido.');
        }

        $pedido->load('detalles');

        return view('pedidos.show', compact('pedido'));
    }

    public function cancelar(Pedido $pedido)
    {
        // RN-05
        if ($pedido->user_id !== Auth::id()) {
            abort(403, 'Acceso denegado a este pedido.');
        }

        // RN-04: Un pedido en estado 'entregado' es inmutable.
        if ($pedido->estado === 'entregado') {
            abort(409, 'Un pedido entregado no puede ser modificado.');
        }

        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo es posible cancelar pedidos en estado pendiente.');
        }

        $pedido->estado = 'cancelado';
        $pedido->save();

        return redirect('/mis-pedidos')->with('success', "El pedido {$pedido->codigo} ha sido cancelado.");
    }
}
