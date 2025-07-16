<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CondicionIva extends Model
{
    use HasFactory;

    protected $table = 'condiciones_iva';

    protected $fillable = [
        'nombre'
    ];
}