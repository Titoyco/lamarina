<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'cajas_movimientos';

    protected $fillable = [
        'id_caja_sesion',
        'id_usuario',
        'tipo',
        'monto',
        'motivo',
        'comprobante',
    ];

    protected $casts = [
        'monto' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function cajaSesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'id_caja_sesion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function scopeIngresos($query)
    {
        return $query->where('tipo', 'ingreso');
    }

    public function scopeRetiros($query)
    {
        return $query->where('tipo', 'retiro');
    }

    public function esIngreso(): bool
    {
        return $this->tipo === 'ingreso';
    }

    public function esRetiro(): bool
    {
        return $this->tipo === 'retiro';
    }
}
