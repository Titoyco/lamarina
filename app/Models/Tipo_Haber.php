<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_Haber extends Model
{
    use HasFactory;
    protected $table = 'tipo_haberes'; // Asegúrate de que este nombre coincida con el de la tabla en la base de datos

    protected $fillable = ['nombre', 'id'];

    
}
