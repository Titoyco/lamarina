<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class RemitoProveedor extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'remitos_proveedores';

    protected $fillable = [
        'numero_remito',
        'proveedor_id',
        'sucursal_id',
        'fecha_recepcion',
        'usuario_id',
        'estado',
        'total_costo',
        'total_venta_estimado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'total_costo' => 'decimal:2',
        'total_venta_estimado' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RemitoProveedorItem::class, 'remito_id');
    }

    /**
     * Movimientos de inventario asociados a este remito.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(ProductoMovimiento::class, 'referencia_id')
                    ->where('referencia_type', self::class);
    }
}
