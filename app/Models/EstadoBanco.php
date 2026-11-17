<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoBanco extends Model
{
    //
    protected $table = 'estados_banco';
    protected $fillable = ['codigo', 'descripcion'];
}
