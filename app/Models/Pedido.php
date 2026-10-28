<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'codigo',
        'user_id',
        'estado',
        'total',
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
        $hoy = date('Ymd');
        $prefix = "UF-{$hoy}-";
        $cacheKey = "pedido_correlativo_{$hoy}";

        // Inicializar el contador en caché con el correlativo más alto existente hoy en la BD
        if (!Cache::has($cacheKey)) {
            $ultimoCodigo = self::where('codigo', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->value('codigo');

            $ultimoNumero = 0;
            if ($ultimoCodigo && preg_match('/-(\d{4,})$/', $ultimoCodigo, $matches)) {
                $ultimoNumero = (int) $matches[1];
            }

            Cache::add($cacheKey, $ultimoNumero, now()->addDays(2));
        }

        $correlativo = (int) Cache::increment($cacheKey);
        $codigo = $prefix . str_pad((string) $correlativo, 4, '0', STR_PAD_LEFT);

        // Salvaguarda: si por registros previos o huecos ya existiera en BD, avanzar atómicamente
        while (self::where('codigo', $codigo)->exists()) {
            $correlativo = (int) Cache::increment($cacheKey);
            $codigo = $prefix . str_pad((string) $correlativo, 4, '0', STR_PAD_LEFT);
        }

        return $codigo;
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
