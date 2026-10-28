<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProteccionCambiosEstadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_cancel_pending_order(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $pedido = Pedido::create([
            'codigo' => 'UF-20261028-0001',
            'user_id' => $student->id,
            'estado' => 'pendiente',
            'total' => 20.00,
        ]);

        $response = $this->actingAs($student)->post("/mis-pedidos/{$pedido->id}/cancelar");
        $response->assertRedirect('/mis-pedidos');
        $response->assertSessionHas('success');

        $this->assertSame('cancelado', $pedido->fresh()->estado);
    }

    public function test_student_cannot_cancel_order_if_already_in_preparation(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $pedido = Pedido::create([
            'codigo' => 'UF-20261028-0002',
            'user_id' => $student->id,
            'estado' => 'en_preparacion',
            'total' => 20.00,
        ]);

        $response = $this->actingAs($student)->post("/mis-pedidos/{$pedido->id}/cancelar");
        $response->assertSessionHas('error');

        // El estado debe permanecer inalterado en 'en_preparacion'
        $this->assertSame('en_preparacion', $pedido->fresh()->estado);
    }

    public function test_employee_cannot_advance_order_if_already_cancelled(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $employee = User::where('rol', 'empleado')->firstOrFail();
        $pedido = Pedido::create([
            'codigo' => 'UF-20261028-0003',
            'user_id' => $student->id,
            'estado' => 'cancelado',
            'total' => 20.00,
        ]);

        $response = $this->actingAs($employee)->post("/panel/pedidos/{$pedido->id}/estado");
        $response->assertSessionHas('error');

        // El estado debe permanecer en cancelado
        $this->assertSame('cancelado', $pedido->fresh()->estado);
    }

    public function test_employee_cannot_advance_delivered_order_with_status_409(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $employee = User::where('rol', 'empleado')->firstOrFail();
        $pedido = Pedido::create([
            'codigo' => 'UF-20261028-0004',
            'user_id' => $student->id,
            'estado' => 'entregado',
            'total' => 20.00,
        ]);

        $response = $this->actingAs($employee)->post("/panel/pedidos/{$pedido->id}/estado");
        $response->assertStatus(409);
    }

    public function test_student_cannot_cancel_another_students_order(): void
    {
        $this->seed();
        $studentA = User::where('rol', 'estudiante')->firstOrFail();
        $studentB = User::factory()->create(['rol' => 'estudiante']);

        $pedido = Pedido::create([
            'codigo' => 'UF-20261028-0005',
            'user_id' => $studentA->id,
            'estado' => 'pendiente',
            'total' => 15.00,
        ]);

        $response = $this->actingAs($studentB)->post("/mis-pedidos/{$pedido->id}/cancelar");
        $response->assertForbidden();

        $this->assertSame('pendiente', $pedido->fresh()->estado);
    }
}

