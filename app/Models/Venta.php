<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Venta extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'ventas';

    protected $fillable = [
        'id_operacion',
        'id_sucursal',
        'id_caja_sesion',
        'id_usuario',
        'id_punto_venta',
        'tipo_emision',
        'id_cliente',
        'cliente_nombre',
        'cliente_documento',
        'punto_venta',
        'numero_factura',
        'cae',
        'cae_vencimiento',
        'afip_qr',
        'datos_fiscales',
        'comprobante',
        'tipo_comprobante',
        'fecha',
        'medio_pago',
        'id_medio_pago',
        'pagos',
        'numero_cupon',
        'subtotal',
        'recargo',
        'descuento',
        'total',
        'id_credito',
        'cuotas_credito',
        'valor_cuota_credito',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'subtotal' => 'decimal:2',
        'recargo' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'valor_cuota_credito' => 'decimal:2',
        'cuotas_credito' => 'integer',
        'punto_venta' => 'integer',
        'numero_factura' => 'integer',
        'cae_vencimiento' => 'date',
        'datos_fiscales' => 'array',
        'pagos' => 'array',
    ];
    public function getRecargoFinanciacionAttribute(): float
    {
        return (float) ($this->recargo ?? 0);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function cajaSesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'id_caja_sesion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'id_punto_venta');
    }

    public function getEsFiscalHasarAttribute(): bool
    {
        return $this->tipo_emision === 'fiscal_hasar';
    }

    public function getEsArcaDigitalAttribute(): bool
    {
        return $this->tipo_emision === 'arca_wsfe';
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'id_credito');
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class, 'id_medio_pago');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VentaItem::class, 'id_venta');
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

    public function getMedioPagoFormateadoAttribute(): string
    {
        if ($this->medio_pago === 'multipago' && !empty($this->pagos) && is_array($this->pagos)) {
            $nombres = array_map(fn($p) => $p['nombre'] ?? $p['medio_pago'] ?? '', $this->pagos);
            return 'Múltiples (' . implode(' + ', array_filter($nombres)) . ')';
        }
        return match ($this->medio_pago) {
            'credito_personal' => 'Crédito Personal',
            'tarjeta_debito'   => 'Tarjeta Débito',
            'tarjeta_credito'  => 'Tarjeta Crédito',
            'transferencia'    => 'Transferencia',
            'multipago'        => 'Múltiples Medios',
            default            => 'Efectivo',
        };
    }

    public function getEsCreditoPersonalAttribute(): bool
    {
        return $this->medio_pago === 'credito_personal';
    }

    public function getEsConsumidorFinalAttribute(): bool
    {
        return is_null($this->id_cliente);
    }

    public function isPresupuesto(): bool
    {
        return $this->tipo_comprobante === 'PRESUPUESTO' || $this->estado === 'presupuesto';
    }

    public function isRemito(): bool
    {
        return $this->tipo_comprobante === 'REMITO' || $this->estado === 'remito';
    }

    public function isNotaCredito(): bool
    {
        return in_array(strtoupper((string) $this->tipo_comprobante), ['NOTA_CREDITO', 'NOTA_CREDITO_B', 'NOTA_CREDITO_A', 'NOTA_CREDITO_C', 'N_CREDITO_B', 'N_CREDITO_A']);
    }

    public function isFacturaA(): bool
    {
        return $this->tipo_comprobante === 'FACTURA_A';
    }

    public function isFacturaB(): bool
    {
        return $this->tipo_comprobante === 'FACTURA_B' || empty($this->tipo_comprobante);
    }

    public function isFacturaC(): bool
    {
        return $this->tipo_comprobante === 'FACTURA_C';
    }

    public function isFactura(): bool
    {
        return in_array($this->tipo_comprobante, ['FACTURA_A', 'FACTURA_B', 'FACTURA_C', null, '']) && !$this->isPresupuesto() && !$this->isRemito() && !$this->isNotaCredito();
    }

    public function isFiscal(): bool
    {
        return ($this->isFactura() || $this->isNotaCredito()) && !$this->isPresupuesto() && !$this->isRemito();
    }

    public function getLetraComprobanteAttribute(): string
    {
        $tipo = strtoupper((string) $this->tipo_comprobante);
        if ($this->isFacturaA() || $tipo === 'NOTA_CREDITO_A') return 'A';
        if ($this->isFacturaC() || $tipo === 'NOTA_CREDITO_C') return 'C';
        if ($this->isRemito()) return 'R';
        if ($this->isPresupuesto()) return 'P';
        if ($this->isFacturaB() || $tipo === 'NOTA_CREDITO_B' || $tipo === 'NOTA_CREDITO') return 'B';
        return 'X';
    }

    public function getTipoComprobanteNombreAttribute(): string
    {
        return match (strtoupper((string) $this->tipo_comprobante)) {
            'FACTURA_A' => 'Factura A',
            'FACTURA_B' => 'Factura B',
            'FACTURA_C' => 'Factura C',
            'REMITO'    => 'Remito de Entrega',
            'PRESUPUESTO' => 'Presupuesto',
            'NOTA_CREDITO_A' => 'Nota de Crédito A',
            'NOTA_CREDITO_B' => 'Nota de Crédito B',
            'NOTA_CREDITO'   => 'Nota de Crédito',
            default => 'Factura B',
        };
    }

    public function esExentoIva(): bool
    {
        return $this->sucursal ? $this->sucursal->isTdf() : false;
    }

    public function netoGravado(): float
    {
        if ($this->esExentoIva()) {
            return 0.00;
        }
        return round((float) $this->total / 1.21, 2);
    }

    public function ivaLiquidado(): float
    {
        if ($this->esExentoIva()) {
            return 0.00;
        }
        return round((float) $this->total - $this->netoGravado(), 2);
    }
}
