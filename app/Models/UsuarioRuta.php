<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioRuta extends Model
{
    use HasFactory;

    protected $table = 'user_rutas';

    protected $fillable = ['user_id', 'ruta'];
}