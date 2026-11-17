<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SucursalIndice extends Model
{
    protected $table = 'sucursal_indices';
    protected $fillable = ['id_sucursal', 'tipo_credito', 'ultimo_indice'];

    public function sucursal() {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}