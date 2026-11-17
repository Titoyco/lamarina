<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

class Cliente extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'clientes';

    protected $fillable = [
        'dni', 'cuit', 'nombre', 'medio_pago_preferido', 'cbu', 'id_sucursal', 'nro_cuenta', 'tipo_haber',
        'saldo', 'saldo_a_favor', 'limite', 'max_cuotas', 'suspendido', 'obs', 'estado_banco', 'fecha_estado_banco',
        'direccion', 'id_localidad', 'email', 'tel1', 'tel2',
        'familiar', 'tipo_familiar', 'direccion_familiar', 'id_localidad_familiar', 'email_familiar', 'tel_familiar',
        'trabajo', 'direccion_trabajo', 'id_localidad_trabajo', 'tel_trabajo'
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'saldo_a_favor' => 'decimal:2',
        'limite' => 'decimal:2',
        'suspendido' => 'boolean',
    ];

    /**
     * Determina si el cliente tiene historial operativo o deuda que impida su eliminación.
     */
    public function tieneHistorial(): bool
    {
        return $this->creditos()->exists() || (float)$this->saldo > 0;
    }

    // Relación con la Sucursal de alta
    public function sucursal(): BelongsTo {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    // Relación con la Localidad de residencia
    public function localidad(): BelongsTo {
        return $this->belongsTo(Localidad::class, 'id_localidad');
    }

    // Relación con la Localidad del Familiar
    public function localidadFamiliar(): BelongsTo {
        return $this->belongsTo(Localidad::class, 'id_localidad_familiar');
    }

    // Relación con la Localidad del Trabajo
    public function localidadTrabajo(): BelongsTo {
        return $this->belongsTo(Localidad::class, 'id_localidad_trabajo');
    }

    /**
     * Los clientes que este Titular ha autorizado.
     */
    public function autorizados(): BelongsToMany
    {
        return $this->belongsToMany(
            Cliente::class,                // Modelo relacionado (sí mismo)
            'cliente_autorizados',         // Tabla pivot
            'id_titular',                  // Clave foránea local en la pivot
            'id_autorizado'                // Clave foránea externa en la pivot
        )->withPivot(['id', 'relacion_aut', 'fecha_autorizado', 'limite_aut']) // 'id' = PK de cliente_autorizados (usado por creditos.id_autorizado)
        ->withTimestamps();
    }

    /**
     * El o los titulares que han autorizado a este cliente como adicional.
     */
    public function titulares(): BelongsToMany
    {
        return $this->belongsToMany(
            Cliente::class,
            'cliente_autorizados',
            'id_autorizado',
            'id_titular'
        )->withPivot(['relacion_aut', 'fecha_autorizado', 'limite_aut'])
        ->withTimestamps();
    }

    /**
     * Método de conveniencia para obtener el límite real del autorizado.
     * Si tiene un límite específico asignado, usa ese; de lo contrario, 
     * hereda el límite del titular.
     */
    public function getLimiteEfectivoAttribute()
    {
        // Si este cliente actúa como autorizado en una relación
        $relacion = $this->titulares()->first();
        
        if ($relacion && $relacion->pivot->limite_aut !== null) {
            return $relacion->pivot->limite_aut;
        }
        
        // Si no tiene límite asignado o es titular, devuelve su propio límite
        return $this->limite;
    }

    public function creditos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Credito::class, 'id_cliente');
    } 

    // Relación con las novedades del cliente
    public function novedades()
    {
        return $this->hasMany(ClienteNovedad::class, 'id_cliente')->latest();
    }

    // Relación con los recibos de pago / cobranzas
    public function recibos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Recibo::class, 'id_cliente')->latest('fecha');
    }

    // Relación con las ventas / facturación
    public function ventas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Venta::class, 'id_cliente')->latest('fecha');
    }

    /**
     * Evalúa el estado crediticio completo del cliente (mora, suspensión, límite y cuotas).
     */
    public function estadoCrediticio(float $montoSolicitado = 0): array
    {
        $suspendido = (bool) $this->suspendido;
        $limiteEfectivo = (float) ($this->limite_efectivo ?? $this->limite ?? 0);

        // Deuda exigible acumulada en cuotas con saldo impago de créditos no cancelados
        $cuotasImpagas = Cuota::whereHas('credito', function ($q) {
            $q->where('id_cliente', $this->id)
              ->whereNull('fecha_cancelacion');
        })->with(['credito.presentacion'])->where('saldo_actual', '>', 0)->get();

        $deudaAcumulada = (float) $cuotasImpagas->sum('saldo_actual');
        $disponible = max(0, round($limiteEfectivo - $deudaAcumulada, 2));

        // Detección de mora:
        // 1. Cuotas con intereses por mora > 0 y saldo impago
        // 2. Cuotas pertenecientes a lotes/presentaciones cerradas (fecha_fin IS NOT NULL)
        $cuotasEnMora = $cuotasImpagas->filter(function ($c) {
            $tieneInteresMora = (float) $c->intereses > 0;
            $loteCerrado = $c->credito?->presentacion && $c->credito->presentacion->fecha_fin !== null;
            return $tieneInteresMora || $loteCerrado;
        });

        $tieneMora = $cuotasEnMora->isNotEmpty();
        $totalMora = (float) $cuotasEnMora->sum('saldo_actual');

        $detalleMora = [];
        foreach ($cuotasEnMora->take(5) as $c) {
            $numCredito = $c->credito?->full_identificador ?? ('#' . $c->id_credito);
            $lote = $c->credito?->presentacion?->numero ?? '-';
            $detalleMora[] = "Cuota {$c->num_cuota}/{$c->credito?->cantidad_cuotas} (Crédito {$numCredito}, Lote {$lote}) - Saldo: $" . number_format((float)$c->saldo_actual, 2, ',', '.');
        }
        if ($cuotasEnMora->count() > 5) {
            $detalleMora[] = "... y " . ($cuotasEnMora->count() - 5) . " cuotas más con mora.";
        }

        $motivosBloqueo = [];
        if ($suspendido) {
            $motivosBloqueo[] = "El cliente se encuentra SUSPENDIDO para operar créditos.";
        }
        if ($tieneMora) {
            $motivosBloqueo[] = "El cliente registra MORA en " . $cuotasEnMora->count() . " cuota(s) por un total de $" . number_format($totalMora, 2, ',', '.') . ".";
        }
        if ($montoSolicitado > 0 && ($deudaAcumulada + $montoSolicitado) > $limiteEfectivo) {
            $superaPor = ($deudaAcumulada + $montoSolicitado) - $limiteEfectivo;
            $motivosBloqueo[] = "El importe solicitado ($" . number_format($montoSolicitado, 2, ',', '.') . ") supera el límite disponible ($" . number_format($disponible, 2, ',', '.') . ") por $" . number_format($superaPor, 2, ',', '.') . ".";
        }

        $habilitado = empty($motivosBloqueo);

        return [
            'habilitado' => $habilitado,
            'suspendido' => $suspendido,
            'tiene_mora' => $tieneMora,
            'total_mora' => $totalMora,
            'cuotas_mora_count' => $cuotasEnMora->count(),
            'detalle_mora' => $detalleMora,
            'limite_total' => $limiteEfectivo,
            'deuda_acumulada' => $deudaAcumulada,
            'limite_disponible' => $disponible,
            'max_cuotas' => (int) ($this->max_cuotas ?? 4),
            'motivos_bloqueo' => $motivosBloqueo,
        ];
    }
}