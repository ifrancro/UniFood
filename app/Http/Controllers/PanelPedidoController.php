<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function avanzarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        if ($pedido->estado === 'entregado') {
            abort(409, 'El pedido ya fue entregado y no puede ser modificado.');
        }

        if ($pedido->estado === 'cancelado') {
            return back()->with('error', 'El pedido fue cancelado y no puede ser modificado.');
        }

        $validated = $request->validate([
            'estado_esperado' => ['required', Rule::in(array_keys(Pedido::TRANSICIONES))],
        ], [
            'estado_esperado.required' => 'Actualiza la página antes de cambiar el estado del pedido.',
            'estado_esperado.in' => 'El estado del pedido no es válido.',
        ]);
        $estadoEsperado = $validated['estado_esperado'];
        $siguienteEstado = Pedido::TRANSICIONES[$estadoEsperado];

        $actualizados = Pedido::whereKey($pedido->id)->where('estado', $estadoEsperado)
            ->update(['estado' => $siguienteEstado]);

        if ($actualizados === 0) {
            return back()->with('error', 'El pedido cambió mientras lo consultabas. Actualiza la página antes de continuar.');
        }

        return back()->with('success', "El pedido {$pedido->codigo} avanzó al estado: '{$siguienteEstado}'.");
    }
}
