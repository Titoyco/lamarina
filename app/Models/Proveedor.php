<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'legajo',
        'cuit',
        'nombre',
        'contacto',
        'direccion',
        'id_localidad',
        'id_iva',
        'tel1',
        'tel2',
        'email',
        'web',
        'saldo',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function getLegajoFormateadoAttribute(): ?string
    {
        if ($this->legajo === null || $this->legajo === '') {
            return null;
        }
        return str_pad($this->legajo, 3, '0', STR_PAD_LEFT);
    }

    public static function sugerirSiguienteLegajo(): string
    {
        $allLegajos = static::whereNotNull('legajo')->pluck('legajo');
        $max = 0;
        foreach ($allLegajos as $leg) {
            if (is_numeric($leg)) {
                $num = (int)$leg;
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        return str_pad((string)($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'id_localidad');
    }

    public function condicionIva()
    {
        return $this->belongsTo(CondicionIva::class, 'id_iva');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function tieneOperaciones(): bool
    {
        return $this->productos()->exists() || (float)$this->saldo != 0;
    }
}
