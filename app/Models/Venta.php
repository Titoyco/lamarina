<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    // Lista explícita de atributos asignables masivamente
    protected $fillable = [
        'id_comprobante',
        'id_sucursal',
        'nro_comprobante',
        'id_cliente',
        'id_usuario',
        'id_terminal',
        'bruto',
        'descuento',
        'iva',
        'totalneto',
        'id_formapago'
    ];
    
    // Si prefieres, puedes usar guarded en vez de fillable, pero fillable es más recomendable para controlar.
    // protected $guarded = ['id', 'created_at', 'updated_at'];
}