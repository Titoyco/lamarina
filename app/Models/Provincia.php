<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $table = 'provincias'; // Asegúrate de que este nombre coincida con el de la tabla en la base de datos

    protected $fillable = ['nombre', 'id'];
    public function localidades()
    {
        return $this->hasMany(Localidad::class, 'id_provincia');
    }
}
