<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PuntoVenta extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'puntos_venta';

    protected $fillable = [
        'id_sucursal',
        'numero',
        'nombre',
        'tipo_emision',
        'configuracion',
        'activo',
    ];

    protected $casts = [
        'numero'        => 'integer',
        'configuracion' => 'array',
        'activo'        => 'boolean',
    ];

    /**
     * Relación con la sucursal emisora.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * Accesor para mostrar el número con formato legal de 5 dígitos (ej: 00003).
     */
    public function getNumeroFormateadoAttribute(): string
    {
        return str_pad((string) $this->numero, 5, '0', STR_PAD_LEFT);
    }
}

