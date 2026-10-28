<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PedidoSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_rejects_a_total_above_database_capacity_without_consuming_the_token(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $products = Producto::limit(3)->get();
        foreach ($products as $product) {
            $product->update(['precio' => '999999.99', 'disponible' => true]);
        }
        $token = (string) Str::uuid();
        $cart = $products->mapWithKeys(fn (Producto $product): array => [$product->id => 50])->all();

        $this->actingAs($student)->withSession(['order_token' => $token, 'carrito' => $cart])
            ->post('/pedidos', ['order_token' => $token])->assertSessionHasErrors('total');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertSame($token, session('order_token'));
    }

    public function test_admin_can_omit_the_optional_product_description(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/admin/productos', [
            'nombre' => 'Sin descripción', 'precio' => '1.00', 'categoria' => 'snacks',
        ])->assertRedirect('/admin/productos')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productos', ['nombre' => 'Sin descripción', 'descripcion' => null]);
    }

    #[TestWith(['/carrito/agregar', 51])]
    #[TestWith(['/carrito/actualizar', 51])]
    public function test_cart_rejects_quantities_above_the_displayed_limit(string $endpoint, int $quantity): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $this->actingAs($student)->post($endpoint, ['producto_id' => $product->id, 'cantidad' => $quantity])
            ->assertSessionHasErrors('cantidad');

        $this->assertSame([], session('carrito', []));
    }

    public function test_repeated_additions_cannot_exceed_fifty_units(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $this->actingAs($student)->withSession(['carrito' => [$product->id => 50]])
            ->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 1])
            ->assertSessionHasErrors('cantidad');

        $this->assertSame([$product->id => 50], session('carrito'));
    }

    #[TestWith(['1.005'])]
    #[TestWith(['1000000.00'])]
    public function test_product_price_must_fit_the_supported_precision_and_range(string $price): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/admin/productos', [
            'nombre' => 'Precio inválido', 'precio' => $price, 'categoria' => 'snacks',
        ])->assertSessionHasErrors('precio');

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_confirmation_requires_a_token_and_keeps_the_cart_on_rejection(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $this->actingAs($student)->withSession(['carrito' => [$product->id => 1]])
            ->from('/carrito')->post('/pedidos')
            ->assertRedirect('/carrito')->assertSessionHasErrors('order_token');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertSame([$product->id => 1], session('carrito'));
    }

    public function test_confirmation_rejects_a_token_not_issued_to_the_session(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);

        $this->actingAs($student)->withSession(['order_token' => (string) Str::uuid()])
            ->from('/carrito')->post('/pedidos', ['order_token' => (string) Str::uuid()])
            ->assertRedirect('/carrito')->assertSessionHasErrors('order_token');

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_replaying_a_confirmation_with_a_stale_session_keeps_one_order(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();
        $token = (string) Str::uuid();
        $staleSession = ['order_token' => $token, 'carrito' => [$product->id => 2]];

        $this->actingAs($student)->withSession($staleSession)
            ->post('/pedidos', ['order_token' => $token])->assertSessionHas('success');
        $this->withSession($staleSession)->post('/pedidos', ['order_token' => $token])
            ->assertRedirect('/mis-pedidos')->assertSessionHas('info');

        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseCount('detalle_pedidos', 1);
        $this->assertDatabaseHas('pedidos', ['user_id' => $student->id, 'order_token' => $token]);
        $this->assertNull(session('order_token'));
        $this->assertSame([], session('carrito', []));
    }

    public function test_replaying_an_old_confirmation_preserves_a_new_cart(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();
        $oldToken = (string) Str::uuid();
        $newToken = (string) Str::uuid();
        $this->actingAs($student)->withSession(['order_token' => $oldToken, 'carrito' => [$product->id => 1]])
            ->post('/pedidos', ['order_token' => $oldToken])->assertSessionHas('success');

        $this->withSession(['order_token' => $newToken, 'carrito' => [$product->id => 3]])
            ->post('/pedidos', ['order_token' => $oldToken])->assertSessionHas('info');

        $this->assertSame($newToken, session('order_token'));
        $this->assertSame([$product->id => 3], session('carrito'));
        $this->assertDatabaseCount('pedidos', 1);
    }

    public function test_unavailable_product_does_not_consume_the_confirmation_token(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();
        $product->update(['disponible' => false]);
        $token = (string) Str::uuid();

        $this->actingAs($student)->withSession(['order_token' => $token, 'carrito' => [$product->id => 1]])
            ->post('/pedidos', ['order_token' => $token])
            ->assertRedirect('/carrito')->assertSessionHas('error');

        $this->assertSame($token, session('order_token'));
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_repeating_an_employee_action_does_not_skip_a_state(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);
        $employee = User::factory()->create(['rol' => 'empleado']);
        $order = Pedido::create(['codigo' => 'UF-STATE', 'user_id' => $student->id, 'estado' => 'pendiente', 'total' => '10.00']);

        $this->actingAs($employee)->post("/panel/pedidos/{$order->id}/estado", ['estado_esperado' => 'pendiente'])
            ->assertRedirect()->assertSessionHas('success');
        $this->post("/panel/pedidos/{$order->id}/estado", ['estado_esperado' => 'pendiente'])
            ->assertRedirect()->assertSessionHas('error');

        $this->assertSame('en_preparacion', $order->fresh()->estado);
    }

    public function test_employee_must_send_the_state_shown_in_the_form(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);
        $employee = User::factory()->create(['rol' => 'empleado']);
        $order = Pedido::create(['codigo' => 'UF-MISSING-STATE', 'user_id' => $student->id, 'estado' => 'pendiente', 'total' => '10.00']);

        $this->actingAs($employee)->post("/panel/pedidos/{$order->id}/estado")
            ->assertSessionHasErrors('estado_esperado');

        $this->assertSame('pendiente', $order->fresh()->estado);
    }

    public function test_cart_and_order_use_exact_decimal_amounts(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();
        $product->update(['precio' => '0.10']);

        $this->actingAs($student)->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 3]);
        $this->get('/carrito')->assertOk()->assertViewHas('total', '0.30');
        $this->post('/pedidos', ['order_token' => session('order_token')])->assertSessionHas('success');

        $this->assertSame('0.30', Pedido::sole()->total);
        $this->assertSame('0.30', Pedido::sole()->detalles()->sole()->subtotal);
    }
}
