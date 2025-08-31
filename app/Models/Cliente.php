<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

        // Define los atributos que se pueden asignar de forma masiva
        protected $fillable = [
            'dni',
            'nombre',
            'medio_pago_preferido',
            'cbu',
            'sucursal',
            'tipo_haber',
            'saldo',
            'limite',
            'max_cuotas',
            'suspendido',
            'obs',
            'direccion',
            'id_localidad',
            'email',
            'tel1',
            'tel2',
            'familiar',
            'tipo_familiar',
            'direccion_familiar',
            'id_localidad_familiar',
            'email_familiar',
            'tel_familiar',
            'trabajo',
            'direccion_trabajo',
            'id_localidad_trabajo',
            'tel_trabajo'
        ];
    protected $table = 'clientes';

        // Definir la relación con Localidad
        public function localidad()
        {
            return $this->belongsTo(Localidad::class, 'id_localidad');
        }
        
        public function localidadFamiliar()
        {
            return $this->belongsTo(Localidad::class, 'id_localidad_familiar');
        }
    
        public function localidadTrabajo()
        {
            return $this->belongsTo(Localidad::class, 'id_localidad_trabajo');
        }
        
        public function haber()
        {
            return $this->belongsTo(Tipo_Haber::class, 'tipo_haber'); 
        }
        public function creditos()
        {
            return $this->hasMany(Credito::class, 'id_cliente');
        }
        public function sucursal()
        {
            return $this->belongsTo(Sucursal::class, 'id_sucursal');
        }
}
