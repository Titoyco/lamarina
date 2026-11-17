<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Presentacion extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'presentaciones';

    protected $fillable = [
        'id_provincia',    // 1: Chubut, 2: Tierra del Fuego
        'numero',          // Número de lote o presentación (ej: 500, 510, etc.)
        'fecha_inicio',
        'fecha_fin',
        'interes_credito', // % de financiación base del lote
        'interes_mora',    // % de mora aplicado al cerrar el lote
        'tasa_cuotas',
        'tasa_mora',
        'primera_cuota_sin_interes', // true: 1 pago sin interés y +tasa por cada cuota adicional
    ];

    protected $casts = [
        'primera_cuota_sin_interes' => 'boolean',
    ];

    /**
     * Cantidad de "tramos" de interés que se aplican según las cuotas.
     *   Primera cuota sin interés: 1c=0, 2c=1, 3c=2, 4c=3 ...
     *   Sin beneficio:              1c=1, 2c=2, 3c=3, 4c=4 ...
     */
    public function tramosInteres(int $cuotas): int
    {
        $cuotas = max(1, $cuotas);
        $sinInteres = $this->primera_cuota_sin_interes ?? true;
        return $sinInteres ? $cuotas - 1 : $cuotas;
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function scopeTdf($query)
    {
        return $query->where('id_provincia', 2);
    }

    public function scopeChubut($query)
    {
        return $query->where('id_provincia', 1);
    }

    public function scopePorProvincia($query, $idProvincia)
    {
        return $query->where('id_provincia', $idProvincia);
    }

    public function scopeAbierta($query)
    {
        return $query->whereNull('fecha_fin');
    }

    protected static ?array $activeNumbersCache = null;

    /**
     * Obtiene la presentación vigente / abierta para una provincia específica.
     */
    public static function activaParaProvincia($idProvincia): ?self
    {
        return self::where('id_provincia', $idProvincia)
                   ->whereNull('fecha_fin')
                   ->orderByDesc('numero')
                   ->first();
    }

    /**
     * Obtiene de forma memoizada el número de presentación abierta para una provincia.
     */
    public static function getNumeroActivaParaProvincia(int $idProvincia): int
    {
        if (self::$activeNumbersCache === null) {
            self::$activeNumbersCache = self::whereNull('fecha_fin')
                ->pluck('numero', 'id_provincia')
                ->toArray();
        }

        return (int) (self::$activeNumbersCache[$idProvincia] ?? 0);
    }

    public static function clearActiveNumbersCache(): void
    {
        self::$activeNumbersCache = null;
    }

    /**
     * Obtiene la presentación vigente para una sucursal dada según su jurisdicción.
     */
    public static function activaParaSucursal($sucursalId): ?self
    {
        $idProvincia = in_array((int)$sucursalId, [1, 2]) ? 2 : 1;
        return self::activaParaProvincia($idProvincia);
    }

    /**
     * Relación: Una presentación engloba muchos créditos 
     * (según el número base del crédito).
     */
    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_presentacion');
    }
}