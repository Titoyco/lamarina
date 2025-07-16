<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    use HasFactory;

    protected $table = 'localidades'; // Asegúrate de que este nombre coincida con el de la tabla en la base de datos

    protected $fillable = ['nombre', 'id', 'id_provincia'];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }
}
