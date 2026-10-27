<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniFoodExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_order_and_staff_can_complete_the_pickup(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $employee = User::where('rol', 'empleado')->firstOrFail();
        $product = Producto::where('nombre', 'Café Americano')->firstOrFail();

        $this->actingAs($student)->get('/catalogo')->assertOk()->assertSee($product->nombre);
        $this->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 2])
            ->assertRedirect('/carrito');
        $this->get('/carrito')->assertOk()->assertSee('Pago al recoger')->assertSee('16.00');
        $this->post('/pedidos')->assertRedirect('/mis-pedidos');

        $pedido = Pedido::firstOrFail();
        $this->assertSame('16.00', $pedido->total);
        $this->assertSame($student->id, $pedido->user_id);
        $this->assertDatabaseHas('detalle_pedidos', ['pedido_id' => $pedido->id, 'cantidad' => 2]);
        $this->get('/mis-pedidos/'.$pedido->id)->assertOk()->assertSee($pedido->codigo);

        $this->actingAs($employee)->get('/panel/pedidos')->assertOk()->assertSee($pedido->codigo);
        foreach (['en_preparacion', 'listo', 'entregado'] as $state) {
            $this->post('/panel/pedidos/'.$pedido->id.'/estado')->assertRedirect();
            $this->assertSame($state, $pedido->fresh()->estado);
        }
        $this->post('/panel/pedidos/'.$pedido->id.'/estado')->assertStatus(409);
    }

    public function test_students_cannot_view_another_students_order_or_access_the_panel(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $owner = User::factory()->create(['rol' => 'estudiante']);
        $pedido = Pedido::create(['codigo' => 'UF-PRIVATE', 'user_id' => $owner->id, 'total' => 8, 'estado' => 'pendiente']);

        $this->actingAs($student)->get('/mis-pedidos')->assertOk()->assertDontSee('UF-PRIVATE');
        $this->get('/mis-pedidos/'.$pedido->id)->assertForbidden();
        $this->get('/panel/pedidos')->assertForbidden();
    }

    public function test_unavailable_products_cannot_be_added_and_empty_orders_are_rejected(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();
        $product->update(['disponible' => false]);

        $this->actingAs($student)->post('/carrito/agregar', ['producto_id' => $product->id, 'cantidad' => 1])
            ->assertSessionHas('error');
        $this->post('/pedidos')->assertRedirect('/carrito')->assertSessionHas('error');
        $this->assertDatabaseCount('pedidos', 0);
        $this->get('/carrito')->assertOk()->assertSee('Tu carrito está vacío');
    }

    public function test_admin_product_and_authentication_screens_render(): void
    {
        $this->seed();
        $this->get('/login')->assertOk();
        $this->get('/registro')->assertOk();
        $this->get('/password/olvide')->assertOk();
        $this->get('/password/reset/example?email=estudiante@unifood.bo')->assertOk();
        $admin = User::where('rol', 'admin')->firstOrFail();

        $this->actingAs($admin)->get('/admin/productos')->assertOk();
        $this->get('/admin/productos/crear')->assertOk();
        $this->get('/admin/productos/'.Producto::firstOrFail()->id.'/editar')->assertOk();
    }
}
