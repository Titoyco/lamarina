<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Sucursal extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sucursales';
    protected $fillable = [
        'nombre',
        'codigo_sucursal',
        'direccion',
        'id_provincia',
        'ultimo_recibo',
        'ultimo_comprobante',
        'nombre_fantasia',
        'razon_social',
        'cuit',
        'condicion_iva',
        'ingresos_brutos',
        'domicilio_fiscal',
        'telefono',
        'email',
        'pagina_web',
        'inicio_actividades',
    ];

    public function getNombreFantasiaAttribute($value): string
    {
        return $value ?: 'La Marina';
    }

    public function getPaginaWebAttribute($value): string
    {
        return $value ?: 'www.tiendaslamarina.com.ar';
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function isTdf(): bool
    {
        return $this->id_provincia == 2 || in_array($this->id, [1, 2]);
    }

    public function isChubut(): bool
    {
        return $this->id_provincia == 1 || in_array($this->id, [3, 4]);
    }

    public function indices(): HasMany
    {
        return $this->hasMany(SucursalIndice::class, 'id_sucursal');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_sucursal');
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_sucursal');
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class, 'id_sucursal');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_sucursal');
    }

    public function puntosVenta(): HasMany
    {
        return $this->hasMany(PuntoVenta::class, 'id_sucursal');
    }

    /**
     * Incrementa atómicamente el correlativo de recibos de la sucursal y retorna el nuevo número.
     */
    public function siguienteNumeroRecibo(): int
    {
        $this->increment('ultimo_recibo');
        return (int) $this->fresh()->ultimo_recibo;
    }

    /**
     * Incrementa atómicamente el correlativo de ventas / comprobantes de la sucursal y retorna el nuevo número.
     */
    public function siguienteNumeroComprobante(): int
    {
        $this->increment('ultimo_comprobante');
        return (int) $this->fresh()->ultimo_comprobante;
    }

    /**
     * Formatea el punto de venta y número correlativo en formato estándar: 0002-00000001
     */
    public function formatearComprobante(int $numero): string
    {
        $pv = str_pad($this->id, 4, '0', STR_PAD_LEFT);
        $num = str_pad($numero, 8, '0', STR_PAD_LEFT);
        return "{$pv}-{$num}";
    }
}
