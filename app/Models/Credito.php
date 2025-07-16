<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';

    // Definir la relación con Cuotas
    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'id_credito');
    }

       // Relaciona el campo codigo_sucursal con el campo codigo de Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'codigo_sucursal', 'codigo');
    }

           // Relaciona el campo codigo_sucursal con el campo codigo de Sucursal
    public function tipo_credito()
    {
        return $this->belongsTo(Sucursal::class, 'tipo_credito', 'codigo');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    // Definir las reglas de validación para la combinación única
    public static function boot()
    {
        parent::boot();

        static::creating(function ($credito) {
            // Verificar si ya existe un crédito con el mismo id_credito y terminal
            if (self::where('credito', $credito->credito)
                ->where('sucursal', $credito->sucursal)
                ->exists()) {
                throw new \Exception('La combinación de credito y terminal debe ser única.');
            }
        });
    }
}
