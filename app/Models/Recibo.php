<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Recibo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'recibos';

    protected $fillable = [
        'id_sucursal',
        'id_cliente',
        'id_user',
        'numero_recibo',
        'fecha',
        'importe_total',
        'saldo_a_favor_generado',
        'medio_pago',
        'id_medio_pago',
        'observaciones',
        'anulado',
        'fecha_anulacion',
        'motivo_anulacion',
        'user_anulacion_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'importe_total' => 'decimal:2',
        'saldo_a_favor_generado' => 'decimal:2',
        'anulado' => 'boolean',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class, 'id_medio_pago');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function userAnulacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_anulacion_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ReciboDetalle::class, 'id_recibo');
    }

    public function getNumeroFormateadoAttribute(): string
    {
        $pad = str_pad($this->numero_recibo, 8, '0', STR_PAD_LEFT);
        $sucCod = $this->sucursal?->codigo_sucursal ?? 'X';
        return "REC-{$pad}-{$sucCod}";
    }

    public function scopeActivos($query)
    {
        return $query->where('anulado', false);
    }

    public function scopeAnulados($query)
    {
        return $query->where('anulado', true);
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('id_sucursal', $sucursalId);
    }

    public function scopePorProvincia($query, $provinciaId)
    {
        return $query->whereHas('sucursal', function ($q) use ($provinciaId) {
            $q->where('id_provincia', $provinciaId);
        });
    }
}
