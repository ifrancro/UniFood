<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EvitarPedidosDuplicadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_generates_order_token_for_safe_submission(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $this->actingAs($student)
            ->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 1]);

        $response = $this->get('/carrito');
        $response->assertOk();
        $response->assertSee('name="order_token"', false);
        $response->assertSee('data-confirm-order-btn', false);
        $this->assertNotNull(session('order_token'));
    }

    public function test_duplicate_submission_with_same_token_creates_only_one_order(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $this->actingAs($student)
            ->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 1]);

        $this->get('/carrito');
        $token = session('order_token');
        $this->assertNotEmpty($token);

        // Primer envío (exitoso)
        $firstResponse = $this->post('/pedidos', ['order_token' => $token]);
        $firstResponse->assertRedirect('/mis-pedidos');
        $firstResponse->assertSessionHas('success');
        $this->assertDatabaseCount('pedidos', 1);

        // Segundo envío simulando doble clic con el mismo token
        $secondResponse = $this->post('/pedidos', ['order_token' => $token]);
        $secondResponse->assertRedirect('/mis-pedidos');
        // Debe mantenerse exactamente en 1 pedido (sin duplicados)
        $this->assertDatabaseCount('pedidos', 1);
    }

    public function test_concurrent_order_submission_is_blocked_by_user_lock(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $this->actingAs($student)
            ->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 1]);

        // Simular que otra petición está en vuelo y tiene el lock
        $this->get('/carrito');
        $token = session('order_token');
        $lock = Cache::lock("pedido_store_user_{$student->id}", 10);
        $this->assertTrue($lock->get());

        // La segunda petición concurrente intenta crear el pedido
        $response = $this->post('/pedidos', ['order_token' => $token]);
        $response->assertRedirect('/mis-pedidos');
        $response->assertSessionHas('info');

        // No se creó ningún pedido duplicado
        $this->assertDatabaseCount('pedidos', 0);

        $lock->release();
    }
}
