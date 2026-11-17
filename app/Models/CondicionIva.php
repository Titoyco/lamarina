<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CondicionIva extends Model
{
    use HasFactory;

    protected $table = 'condiciones_iva';

    protected $fillable = [
        'nombre',
        'codigo_afip'
    ];

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'id_iva');
    }
}
