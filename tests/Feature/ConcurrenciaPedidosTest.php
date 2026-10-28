<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrenciaPedidosTest extends TestCase
{
    private string $databasePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'unifood-concurrency-');
        config(['database.connections.sqlite.database' => $this->databasePath]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true, '--no-interaction' => true])->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        foreach ([$this->databasePath, $this->databasePath.'-wal', $this->databasePath.'-shm'] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    public function test_two_processes_confirming_the_same_token_create_one_complete_order(): void
    {
        $this->seed();
        $student = User::where('rol', 'estudiante')->firstOrFail();
        $product = Producto::firstOrFail();

        $results = $this->runTogether('order', $student->id, $product->id, (string) Str::uuid());

        $this->assertSame([302, 302], array_column($results, 'status'));
        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseCount('detalle_pedidos', 1);
        $this->assertSame($student->id, Pedido::sole()->user_id);
    }

    public function test_two_processes_reserve_distinct_daily_codes(): void
    {
        $results = $this->runTogether('code');

        $this->assertNotSame($results[0]['code'], $results[1]['code']);
    }

    public function test_two_processes_advancing_the_same_displayed_state_advance_only_once(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);
        $employee = User::factory()->create(['rol' => 'empleado']);
        $order = Pedido::create(['codigo' => 'UF-PARALLEL', 'user_id' => $student->id, 'estado' => 'pendiente', 'total' => '10.00']);

        $results = $this->runTogether('advance', $employee->id, $order->id);

        $this->assertSame([302, 302], array_column($results, 'status'));
        $this->assertSame('en_preparacion', $order->fresh()->estado);
    }

    public function test_cancellation_and_preparation_compete_without_overwriting_each_other(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);
        $employee = User::factory()->create(['rol' => 'empleado']);
        $order = Pedido::create(['codigo' => 'UF-CANCEL-RACE', 'user_id' => $student->id, 'estado' => 'pendiente', 'total' => '10.00']);

        $results = $this->runTogether('race', $student->id, $order->id, otherUserId: $employee->id);

        $this->assertSame([302, 302], array_column($results, 'status'));
        $this->assertSame(1, array_sum(array_column($results, 'changed')));
        $this->assertContains($order->fresh()->estado, ['cancelado', 'en_preparacion']);
    }

    public function test_schema_upgrade_preserves_old_orders_and_continues_their_sequence(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 1, '--force' => true, '--no-interaction' => true])->assertExitCode(0);
        $student = User::factory()->create(['rol' => 'estudiante']);
        $prefix = 'UF-'.now()->format('Ymd').'-';
        $order = Pedido::create(['codigo' => $prefix.'0099', 'user_id' => $student->id, 'estado' => 'pendiente', 'total' => '12.50']);

        $this->artisan('migrate', ['--force' => true, '--no-interaction' => true])->assertExitCode(0);

        $this->assertSame('12.50', $order->fresh()->total);
        $this->assertNull($order->fresh()->order_token);
        $this->assertSame($prefix.'0100', Pedido::generarCodigo());
        $this->assertDatabaseCount('pedidos', 1);
    }

    /** @return list<array{status?: int, code?: string, changed?: bool}> */
    private function runTogether(string $mode, int $userId = 0, int $recordId = 0, string $token = '', int $otherUserId = 0): array
    {
        $worker = <<<'PHP'
        require $argv[1].'/vendor/autoload.php';
        $app = require $argv[1].'/bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app['session']->start();
        $mode = $argv[2];
        if ($mode !== 'code') {
            Illuminate\Support\Facades\Auth::setUser(App\Models\User::findOrFail((int) $argv[3]));
        }
        $order = in_array($mode, ['advance', 'cancel']) ? App\Models\Pedido::findOrFail((int) $argv[4]) : null;
        $request = Illuminate\Http\Request::create('/', 'POST', [
            'order_token' => $argv[5], 'estado_esperado' => 'pendiente',
        ]);
        $request->setLaravelSession($app['session']->driver());
        $app->instance('request', $request);
        $app['session']->put(['order_token' => $argv[5], 'carrito' => [(int) $argv[4] => 2]]);
        touch($argv[6]);
        $deadline = microtime(true) + 10;
        while (!is_file($argv[7])) {
            if (microtime(true) > $deadline) { throw new RuntimeException('La barrera no se liberó'); }
            usleep(10000);
        }
        if ($mode === 'code') {
            echo json_encode(['code' => App\Models\Pedido::generarCodigo()]);
        } elseif ($mode === 'order') {
            $response = (new App\Http\Controllers\PedidoController)->store($request);
            echo json_encode(['status' => $response->getStatusCode()]);
        } elseif ($mode === 'cancel') {
            $response = (new App\Http\Controllers\PedidoController)->cancelar($order);
            echo json_encode(['status' => $response->getStatusCode(), 'changed' => session()->has('success')]);
        } else {
            $response = (new App\Http\Controllers\PanelPedidoController)->avanzarEstado(
                $request, $order
            );
            echo json_encode(['status' => $response->getStatusCode(), 'changed' => session()->has('success')]);
        }
        PHP;
        $release = $this->databasePath.'.release';
        $processes = [];

        try {
            foreach ([0, 1] as $index) {
                $workerMode = $mode === 'race' ? ($index === 0 ? 'cancel' : 'advance') : $mode;
                $workerUserId = $mode === 'race' && $index === 1 ? $otherUserId : $userId;
                $process = new Process([
                    PHP_BINARY, '-r', $worker, base_path(), $workerMode, (string) $workerUserId,
                    (string) $recordId, $token, $release.'.ready'.$index, $release,
                ], base_path(), [
                    'APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite',
                    'DB_DATABASE' => $this->databasePath, 'DB_URL' => false,
                    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
                ]);
                $process->setTimeout(20);
                $process->start();
                $processes[] = $process;
            }

            $deadline = microtime(true) + 10;
            while (! is_file($release.'.ready0') || ! is_file($release.'.ready1')) {
                if (microtime(true) >= $deadline) {
                    $this->fail('Los dos procesos deben alcanzar la barrera.');
                }
                usleep(10000);
            }
            touch($release);

            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput().$process->getOutput());
                $results[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach ([$release, $release.'.ready0', $release.'.ready1'] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }
}
