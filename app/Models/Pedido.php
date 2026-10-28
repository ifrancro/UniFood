<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'codigo',
        'user_id',
        'estado',
        'total',
        'order_token',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public const TRANSICIONES = [
        'pendiente' => 'en_preparacion',
        'en_preparacion' => 'listo',
        'listo' => 'entregado',
    ];

    public const TEXTO_SIGUIENTE_ESTADO = [
        'pendiente' => 'Iniciar preparación',
        'en_preparacion' => 'Marcar listo',
        'listo' => 'Marcar entregado',
    ];

    public static function generarCodigo(): string
    {
        return DB::transaction(function (): string {
            $fecha = now()->format('Y-m-d');
            $prefijo = 'UF-'.now()->format('Ymd').'-';
            $nuevoContador = DB::table('pedido_contadores')->insertOrIgnore([
                'fecha' => $fecha,
                'ultimo_numero' => 0,
            ]);

            if ($nuevoContador) {
                $ultimoCodigo = self::where('codigo', 'like', $prefijo.'%')
                    ->orderByRaw('LENGTH(codigo) DESC')->orderByDesc('codigo')->value('codigo');

                if ($ultimoCodigo && preg_match('/-(\d{4,})$/', $ultimoCodigo, $coincidencias)) {
                    DB::table('pedido_contadores')->where('fecha', $fecha)
                        ->update(['ultimo_numero' => (int) $coincidencias[1]]);
                }
            }

            do {
                DB::table('pedido_contadores')->where('fecha', $fecha)->increment('ultimo_numero');
                $numero = DB::table('pedido_contadores')->where('fecha', $fecha)->value('ultimo_numero');
                $codigo = $prefijo.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            } while (self::where('codigo', $codigo)->exists());

            return $codigo;
        }, attempts: 5);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }
}
