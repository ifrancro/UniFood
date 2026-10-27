<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }
}
