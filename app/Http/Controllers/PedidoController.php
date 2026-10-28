<?php

namespace App\Http\Controllers;

use App\Importe;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_token' => ['required', 'uuid'],
        ], [
            'order_token.required' => 'Vuelve al carrito y confirma el pedido desde allí.',
            'order_token.uuid' => 'La confirmación no es válida. Vuelve a abrir el carrito.',
        ]);
        $token = $validated['order_token'];
        $userId = Auth::id();

        if (Pedido::where('user_id', $userId)->where('order_token', $token)->exists()) {
            return $this->pedidoYaProcesado($token);
        }

        $sessionToken = session('order_token');
        if (! is_string($sessionToken) || ! hash_equals($sessionToken, $token)) {
            throw ValidationException::withMessages([
                'order_token' => 'La confirmación no corresponde a tu carrito. Vuelve a abrirlo.',
            ]);
        }

        $lock = Cache::lock("pedido_store_user_{$userId}", 10);

        if (! $lock->get()) {
            return redirect('/mis-pedidos')->with('info', 'Tu pedido ya se está procesando. Por favor, espera un momento.');
        }

        try {
            try {
                $resultado = DB::transaction(function () use ($token, $userId): Pedido|RedirectResponse {
                    $existente = Pedido::where('user_id', $userId)->where('order_token', $token)->first();
                    if ($existente) {
                        return $existente;
                    }

                    // 1. Leer el carrito de sesión. Si está vacío -> back con error (RN-02).
                    $carrito = session('carrito', []);

                    if (empty($carrito)) {
                        if (session()->has('pedido_reciente_id')) {
                            return redirect('/mis-pedidos');
                        }

                        return redirect('/carrito')->with('error', 'El carrito está vacío. Agrega al menos un producto.');
                    }

                    // 2. Cargar productos por id desde BD.
                    $productoIds = array_keys($carrito);
                    $productos = Producto::whereIn('id', $productoIds)->lockForUpdate()->get()->keyBy('id');

                    $invalidoNombre = null;
                    foreach ($carrito as $pId => $cantidad) {
                        $p = $productos->get($pId);
                        if (! $p || ! $p->activo || ! $p->disponible) {
                            $invalidoNombre = $p ? $p->nombre : "Producto #{$pId}";
                            unset($carrito[$pId]);
                        }
                    }

                    if ($invalidoNombre) {
                        session(['carrito' => $carrito]);

                        return redirect('/carrito')->with('error', "El producto '{$invalidoNombre}' ya no se encuentra disponible. Se retiró de tu carrito.");
                    }

                    // 3. Calcular subtotal = precio_bd * cantidad, y total = suma de subtotales (RN-03).
                    $totalCentavos = 0;
                    $detalles = [];

                    foreach ($carrito as $pId => $cantidad) {
                        $qty = (int) $cantidad;
                        if ($qty <= 0) {
                            continue;
                        }

                        $producto = $productos->get($pId);
                        $precioCentavos = Importe::aCentavos($producto->precio);
                        $subtotalCentavos = $precioCentavos * $qty;
                        $totalCentavos += $subtotalCentavos;

                        $detalles[] = [
                            'producto_id' => $producto->id,
                            'nombre_producto' => $producto->nombre,
                            'precio_unitario' => Importe::aDecimal($precioCentavos),
                            'cantidad' => $qty,
                            'subtotal' => Importe::aDecimal($subtotalCentavos),
                        ];
                    }

                    if (empty($detalles)) {
                        return redirect('/carrito')->with('error', 'El carrito no contiene productos válidos.');
                    }

                    if ($totalCentavos > 9_999_999_999) {
                        return redirect('/carrito')->withErrors([
                            'total' => 'El total supera el importe máximo permitido. Reduce los productos de tu pedido.',
                        ]);
                    }

                    // 4. Generar código único y correlativo seguro frente a concurrencia
                    $codigo = Pedido::generarCodigo();

                    // 5. Crear el Pedido (estado 'pendiente') y sus DetallePedido copiando nombre y precio.
                    $pedido = Pedido::create([
                        'codigo' => $codigo,
                        'user_id' => $userId,
                        'order_token' => $token,
                        'estado' => 'pendiente',
                        'total' => Importe::aDecimal($totalCentavos),
                    ]);

                    foreach ($detalles as $detalle) {
                        $pedido->detalles()->create($detalle);
                    }

                    return $pedido;
                }, attempts: 5);
            } catch (UniqueConstraintViolationException $exception) {
                $resultado = Pedido::where('user_id', $userId)->where('order_token', $token)->first();
                if (! $resultado) {
                    throw $exception;
                }
            }

            if ($resultado instanceof RedirectResponse) {
                return $resultado;
            }

            if (! $resultado->wasRecentlyCreated) {
                return $this->pedidoYaProcesado($token);
            }

            session()->forget(['carrito', 'order_token']);
            session()->flash('pedido_reciente_id', $resultado->id);

            return redirect('/mis-pedidos')->with('success', "¡Pedido registrado con éxito! Tu código de pedido es: {$resultado->codigo}");
        } finally {
            $lock->release();
        }
    }

    private function pedidoYaProcesado(string $token): RedirectResponse
    {
        if (session('order_token') === $token) {
            session()->forget(['carrito', 'order_token']);
        }

        return redirect('/mis-pedidos')->with('info', 'Este pedido ya fue procesado.');
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

    public function cancelar(Pedido $pedido): RedirectResponse
    {
        if ($pedido->user_id !== Auth::id()) {
            abort(403, 'Acceso denegado a este pedido.');
        }

        if ($pedido->estado === 'entregado') {
            abort(409, 'Un pedido entregado no puede ser modificado.');
        }

        $actualizados = Pedido::whereKey($pedido->id)->where('user_id', Auth::id())
            ->where('estado', 'pendiente')->update(['estado' => 'cancelado']);

        if ($actualizados === 0) {
            return back()->with('error', 'Solo es posible cancelar pedidos en estado pendiente.');
        }

        return redirect('/mis-pedidos')->with('success', "El pedido {$pedido->codigo} ha sido cancelado.");
    }
}
