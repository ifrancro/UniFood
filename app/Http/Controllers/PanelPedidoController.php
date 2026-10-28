<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelPedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with('user', 'detalles');

        if ($request->filled('estado') && in_array($request->estado, ['pendiente', 'en_preparacion', 'listo', 'entregado', 'cancelado'])) {
            $query->where('estado', $request->estado);
        }

        // Orden: pendientes primero, luego fecha ascendente
        $pedidos = $query->orderByRaw("
            CASE 
                WHEN estado = 'pendiente' THEN 0 
                WHEN estado = 'en_preparacion' THEN 1 
                WHEN estado = 'listo' THEN 2 
                WHEN estado = 'entregado' THEN 3 
                ELSE 4 
            END ASC
        ")->orderBy('created_at', 'asc')->get();

        $estadoSeleccionado = $request->query('estado', '');

        return view('panel.pedidos.index', [
            'pedidos' => $pedidos,
            'estadoSeleccionado' => $estadoSeleccionado,
        ]);
    }

    public function show(Pedido $pedido)
    {
        $pedido->load('user', 'detalles.producto');

        return view('panel.pedidos.show', compact('pedido'));
    }

    public function avanzarEstado(Pedido $pedido)
    {
        return DB::transaction(function () use ($pedido) {
            $pedidoActual = Pedido::where('id', $pedido->id)->lockForUpdate()->firstOrFail();

            // RN-04: Un pedido en estado 'entregado' es inmutable.
            if ($pedidoActual->estado === 'entregado') {
                abort(409, 'El pedido ya fue entregado y no puede ser modificado.');
            }

            if ($pedidoActual->estado === 'cancelado') {
                return back()->with('error', 'El pedido fue cancelado y no puede ser modificado.');
            }

            $siguienteEstado = Pedido::TRANSICIONES[$pedidoActual->estado] ?? null;

            if (!$siguienteEstado) {
                abort(400, 'No se puede avanzar el estado actual de este pedido.');
            }

            $pedidoActual->estado = $siguienteEstado;
            $pedidoActual->save();

            return back()->with('success', "El pedido {$pedidoActual->codigo} avanzó al estado: '{$siguienteEstado}'.");
        });
    }
}
