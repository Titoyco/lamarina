<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;
    protected $table = 'sucursales'; // Asegúrate de que este nombre coincida con el de la tabla en la base de datos

    protected $fillable = ['nombre', 'codigo'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
        // Relación con índices
    public function indices()
    {
        return $this->hasMany(Indice::class, 'sucursal_id');
    }
}
