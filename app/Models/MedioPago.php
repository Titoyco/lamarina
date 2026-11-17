<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class MedioPago extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'medios_pago';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_base',
        'recargo_porcentaje',
        'descuento_porcentaje',
        'requiere_referencia',
        'permite_vuelto',
        'icono',
        'color',
        'orden',
        'activo',
    ];

    protected $casts = [
        'recargo_porcentaje'   => 'float',
        'descuento_porcentaje' => 'float',
        'requiere_referencia'  => 'boolean',
        'permite_vuelto'       => 'boolean',
        'orden'                => 'integer',
        'activo'               => 'boolean',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_medio_pago');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class, 'id_medio_pago');
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class, 'id_medio_pago');
    }

    /**
     * Determina si el medio de pago tiene al menos un movimiento registrado.
     */
    public function tieneMovimientos(): bool
    {
        return $this->ventas()->exists() || $this->cuotas()->exists() || $this->recibos()->exists();
    }

    /**
     * Retorna el recuento total consolidado de movimientos asociados.
     */
    public function getTotalMovimientosAttribute(): int
    {
        return $this->ventas()->count() + $this->cuotas()->count() + $this->recibos()->count();
    }

    /**
     * Scope para consultar solo medios de pago habilitados.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }

    /**
     * Retorna una etiqueta amigable para la categoría base.
     */
    public function getTipoBaseLabelAttribute(): string
    {
        return match ($this->tipo_base) {
            'efectivo' => 'Efectivo Físico',
            'tarjeta'  => 'Tarjeta (Débito/Crédito)',
            'credito'  => 'Crédito Personal / Cta Cte',
            'banco'    => 'Bancario / Transferencia / QR',
            default    => 'Otro Medio',
        };
    }
}
