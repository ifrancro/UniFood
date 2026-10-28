<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GeneracionSeguraCodigoPedidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserved_codes_survive_cache_flush_before_an_order_is_saved(): void
    {
        $firstCode = Pedido::generarCodigo();

        Cache::flush();
        $secondCode = Pedido::generarCodigo();

        $this->assertNotSame($firstCode, $secondCode);
    }

    public function test_generates_standard_format_code(): void
    {
        $codigo = Pedido::generarCodigo();
        $hoy = date('Ymd');

        $this->assertMatchesRegularExpression("/^UF-{$hoy}-\d{4}$/", $codigo);
    }

    public function test_consecutive_generations_produce_unique_increasing_codes(): void
    {
        $code1 = Pedido::generarCodigo();
        $code2 = Pedido::generarCodigo();
        $code3 = Pedido::generarCodigo();

        $this->assertNotSame($code1, $code2);
        $this->assertNotSame($code2, $code3);
        $this->assertNotSame($code1, $code3);

        $num1 = (int) substr($code1, -4);
        $num2 = (int) substr($code2, -4);
        $num3 = (int) substr($code3, -4);

        $this->assertSame($num1 + 1, $num2);
        $this->assertSame($num2 + 1, $num3);
    }

    public function test_generar_codigo_skips_already_persisted_codes(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $hoy = date('Ymd');

        // Insertar un pedido existente con correlativo 0010
        Pedido::create([
            'codigo' => "UF-{$hoy}-0010",
            'user_id' => $student->id,
            'estado' => 'pendiente',
            'total' => 25.00,
        ]);

        // Al generar código con la caché limpia, debe detectar el 0010 y empezar en 0011
        Cache::flush();

        $nuevoCodigo = Pedido::generarCodigo();
        $this->assertSame("UF-{$hoy}-0011", $nuevoCodigo);
    }

    public function test_cache_flush_recovers_state_from_database(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $hoy = date('Ymd');

        $c1 = Pedido::generarCodigo();
        Pedido::create([
            'codigo' => $c1,
            'user_id' => $student->id,
            'estado' => 'pendiente',
            'total' => 10.00,
        ]);

        $c2 = Pedido::generarCodigo();
        Pedido::create([
            'codigo' => $c2,
            'user_id' => $student->id,
            'estado' => 'pendiente',
            'total' => 10.00,
        ]);

        // Simular reinicio de caché
        Cache::flush();

        // El siguiente código no debe colisionar con c1 o c2
        $c3 = Pedido::generarCodigo();
        $this->assertNotSame($c1, $c3);
        $this->assertNotSame($c2, $c3);

        $num2 = (int) substr($c2, -4);
        $num3 = (int) substr($c3, -4);
        $this->assertSame($num2 + 1, $num3);
    }
}
