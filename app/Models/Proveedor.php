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
        'nombre',
        'direccion',
        'id_localidad',
        'tel1',
        'tel2',
        'id_iva',
        'cuit',
        'email',
        'Saldo',
        'web',
        'comentario'
    ];

        // Relación con Producto
        public function productos()
        {
            return $this->hasMany(Producto::class, 'proveedor_id');
        }
}