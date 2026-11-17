<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReciboDetalle extends Model
{
    protected $table = 'recibo_detalles';

    protected $fillable = [
        'id_recibo',
        'id_credito',
        'id_cuota',
        'num_cuota',
        'concepto',
        'importe',
        'saldo_anterior',
        'saldo_nuevo',
    ];

    protected $casts = [
        'importe' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
        'num_cuota' => 'integer',
    ];

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(Recibo::class, 'id_recibo');
    }

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'id_credito');
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class, 'id_cuota');
    }
}
