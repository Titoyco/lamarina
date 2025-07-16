<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    protected $table = 'cuotas';

    // Definir la relación con Credito
    public function credito()
    {
        return $this->belongsTo(Credito::class, 'id_credito');
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class);
    }

    // Método para determinar si la cuota está vencida
    public function estaVencida()
    {
        $presentacionActual = Presentacion::actual();
        return $this->presentacion->numero < $presentacionActual->numero && !$this->pagada;
    }
}