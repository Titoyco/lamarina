<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_credito extends Model
{
    use HasFactory;

    protected $table = 'tipo_credito';

    protected $fillable = ['nombre', 'codigo'];
}