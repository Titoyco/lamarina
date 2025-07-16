<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    protected $fillable = ['numero', 'fecha_inicio', 'actual'];

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    // Método para obtener la presentación actual
    public static function actual()
    {
        return self::where('actual', true)->first();
    }
}