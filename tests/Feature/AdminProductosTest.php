<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductosTest extends TestCase
{
    use RefreshDatabase;

    private function productData(array $changes = []): array
    {
        return array_merge([
            'nombre' => 'Producto prueba admin',
            'descripcion' => 'Descripción de prueba',
            'precio' => '9.50',
            'categoria' => 'snacks',
        ], $changes);
    }

    public function test_admin_can_create_products_with_availability_checked_or_unchecked(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        // An unchecked HTML checkbox may omit the field entirely.
        $this->post('/admin/productos', $this->productData())
            ->assertRedirect('/admin/productos')->assertSessionHasNoErrors();
        $product = Producto::sole();
        $this->assertFalse($product->disponible);
        $this->assertTrue($product->activo);

        $this->post('/admin/productos', $this->productData(['nombre' => 'Producto disponible', 'disponible' => '1']))
            ->assertRedirect('/admin/productos')->assertSessionHasNoErrors();
        $this->assertTrue(Producto::where('nombre', 'Producto disponible')->firstOrFail()->disponible);
    }

    public function test_admin_can_uncheck_both_flags_and_reactivate_the_product(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $student = User::factory()->create(['rol' => 'estudiante']);
        $product = Producto::create($this->productData(['disponible' => true, 'activo' => true]));

        $this->actingAs($admin)->put('/admin/productos/'.$product->id, $this->productData(['precio' => '11.25']))
            ->assertRedirect('/admin/productos')->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertFalse($product->disponible);
        $this->assertFalse($product->activo);
        $this->assertSame('11.25', $product->precio);
        $this->actingAs($student)->get('/catalogo')->assertOk()->assertDontSee($product->nombre);

        $this->actingAs($admin)->put('/admin/productos/'.$product->id, $this->productData(['disponible' => '1', 'activo' => '1']))
            ->assertRedirect('/admin/productos')->assertSessionHasNoErrors();
        $this->assertTrue($product->fresh()->disponible);
        $this->assertTrue($product->fresh()->activo);
        $this->actingAs($student)->get('/catalogo')->assertOk()->assertSee($product->nombre);
    }

    public function test_explicit_zero_from_hidden_form_fields_is_saved_as_false(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->post('/admin/productos', $this->productData(['disponible' => '0']))
            ->assertRedirect('/admin/productos')->assertSessionHasNoErrors();
        $product = Producto::sole();
        $this->assertFalse($product->disponible);
        $this->put('/admin/productos/'.$product->id, $this->productData(['disponible' => '0', 'activo' => '0']))
            ->assertRedirect('/admin/productos')->assertSessionHasNoErrors();
        $this->assertFalse($product->fresh()->activo);
    }

    public function test_invalid_price_does_not_change_a_product(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $product = Producto::create($this->productData(['disponible' => true, 'activo' => true]));
        $this->actingAs($admin)->put('/admin/productos/'.$product->id, $this->productData(['precio' => '-1']))
            ->assertSessionHasErrors('precio');
        $product->refresh();
        $this->assertSame('9.50', $product->precio);
        $this->assertTrue($product->activo);
        $this->assertTrue($product->disponible);
    }

    public function test_students_and_employees_cannot_modify_products(): void
    {
        $product = Producto::create($this->productData(['disponible' => true, 'activo' => true]));
        foreach (['estudiante', 'empleado'] as $role) {
            $user = User::factory()->create(['rol' => $role]);
            $this->actingAs($user)->get('/admin/productos')->assertForbidden();
            $this->post('/admin/productos', $this->productData())->assertForbidden();
            $this->put('/admin/productos/'.$product->id, $this->productData())->assertForbidden();
            $this->post('/admin/productos/'.$product->id.'/disponibilidad')->assertForbidden();
            $this->post('/admin/productos/'.$product->id.'/desactivar')->assertForbidden();
        }
        $this->assertDatabaseCount('productos', 1);
        $this->assertTrue($product->fresh()->activo);
        $this->assertTrue($product->fresh()->disponible);
    }
}
