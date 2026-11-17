<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ClienteAutorizado;
use App\Models\Presentacion;
use App\Models\Sucursal;
use OwenIt\Auditing\Contracts\Auditable;

class Credito extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'creditos';

    protected $fillable = [
        'id_cliente', 
        'id_sucursal', 
        'id_autorizado',
        'id_presentacion',   // Relación con la tabla presentaciones
        'credito',          // Ej: IC124
        'tipo_credito',     // C, D, R
        'importe_original', // Capital inicial 
        'cantidad_cuotas', 
        'valor_cuota_ref',  // Monto fijo pactado por cuota
        'fecha_credito', 
        'fecha_cancelacion'
    ];

    public function cliente(): BelongsTo {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }


    public function cuotas(): HasMany {
        return $this->hasMany(Cuota::class, 'id_credito');
    }

    /**
     * Presentación (lote) ORIGEN del crédito — FUENTE DE VERDAD del lote inicial.
     *
     * ⚠️ El lote donde nació el crédito se resuelve SIEMPRE por la FK relacional
     * `id_presentacion` (esta relación). NO existe columna física `presentacion_origen`
     * en la tabla `creditos`; cualquier consulta que necesite el lote de origen debe
     * hacer INNER JOIN con `presentaciones` vía `creditos.id_presentacion`.
     * El lote exigible actual de una cuota se calcula con `presentaciones.numero + cuotas.num_cuota`
     * (ver Cuota::getLoteCalculadoAttribute / Cuota::lote_calculado).
     */
    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function venta(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Venta::class, 'id_credito');
    }

    /**
     * Genera el identificador único oficial del crédito:
     * - 1er caracter: Tipo de crédito ('C' = Crédito, 'D' = Débito, 'R' = Refinanciación)
     * - Número correlativo: 7 dígitos con ceros a la izquierda (ej: 0001234)
     * - Sufijo: Código de la sucursal (ej: "TW", "IN", "US", "UA")
     *
     * Ejemplos:
     * - Crédito 1234 Trelew: C0001234TW
     * - Crédito 1234 Interior: C0001234IN
     * - Refinanciación 1234 Trelew: R0001234TW
     * - Débito 1234 Trelew: D0001234TW
     * - Crédito 1234 Ushuaia: C0001234US
     */
    public function getFullIdentificadorAttribute(): string
    {
        // 1. Tipo de crédito (C, D o R)
        $tipoPart = strtoupper($this->tipo_credito ?? 'C');
        if ($tipoPart === 'T' || $tipoPart === 'I') {
            $tipoPart = 'C';
        }

        // 2. Número correlativo relleno a 7 dígitos (ej: 0001234)
        $numericOnly = preg_replace('/[^0-9]/', '', (string)$this->credito);
        $numeroCredito = str_pad($numericOnly ?: '0', 7, '0', STR_PAD_LEFT);

        // 3. Código de la sucursal (TW, IN, US, UA)
        $sucursalPart = strtoupper($this->sucursal?->codigo_sucursal ?? 'TW');

        return "{$tipoPart}{$numeroCredito}{$sucursalPart}";
    }
    /**
     * Firma autorizada que respalda el crédito.
     * ⚠️ `creditos.id_autorizado` referencia `cliente_autorizados.id` (NO `clientes.id`).
     * NULL = crédito tomado directamente por el titular.
     */
    public function autorizado(): BelongsTo
    {
        return $this->belongsTo(ClienteAutorizado::class, 'id_autorizado');
    }

    /**
     * Valor de cuota individual efectiva.
     * Retorna el monto pactado por cuota (valor_cuota_ref). Si no está disponible,
     * calcula el valor proporcional o de la primera cuota.
     */
    public function getValorCuotaAttribute(): float
    {
        if ((float) $this->valor_cuota_ref > 0) {
            return (float) $this->valor_cuota_ref;
        }
        if ((int) $this->cantidad_cuotas > 0 && (float) ($this->total_valor_base ?? 0) > 0) {
            return (float) $this->total_valor_base / (int) $this->cantidad_cuotas;
        }
        return (float) ($this->cuotas?->first()?->valor_base ?? 0);
    }


    /**
     * Calcula la cuota en curso ("la cuota que va") respecto al total de cuotas pactadas.
     * Basado en la diferencia entre la presentación activa de la jurisdicción y la
     * presentación en que se originó el crédito:
     * cuota = min(cantidad_cuotas, max(0, pres_actual - pres_origen))
     *
     * Ejemplos:
     * - Origen lote 100, actual lote 102, 4 cuotas => "2 / 4"
     * - Origen lote 100, actual lote 110, 4 cuotas => "4 / 4" (tope alcanzado)
     * - Origen lote 100, actual lote 100, 4 cuotas => "0 / 4"
     */
    public function getCuotaProgresoAttribute(): string
    {
        $totalCuotas = (int) ($this->cantidad_cuotas ?? 1);
        if ($totalCuotas <= 0) {
            return '-';
        }

        $numOrigen = $this->presentacion?->numero;
        if ($numOrigen === null) {
            return "- / {$totalCuotas}";
        }

        // Jurisdicción de la presentación / sucursal
        $idProvincia = $this->presentacion?->id_provincia 
            ?? $this->sucursal?->id_provincia 
            ?? ($this->sucursal?->isTdf() ? 2 : 1);

        $numActual = Presentacion::getNumeroActivaParaProvincia((int)$idProvincia);
        if ($numActual <= 0) {
            return "1 / {$totalCuotas}";
        }

        $diferencia = $numActual - $numOrigen;
        $cuotaActual = min($totalCuotas, max(0, $diferencia));

        return "{$cuotaActual} / {$totalCuotas}";
    }
}