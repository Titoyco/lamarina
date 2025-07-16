<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Indice extends Model
{
    use HasFactory;

    protected $fillable = ['codigo', 'nombre', 'indice', 'sucursal_id'];
    protected $table = 'indices';

    public function creditos()
    {
        return $this->hasMany(Credito::class, 'indice');
    }
    
    // Relación con sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
