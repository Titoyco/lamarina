<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoMovimiento extends Model
{
    protected $table = 'productos_movimientos';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'tipo',
        'cantidad',
        'precio_compra',
        'precio_venta',
        'usuario_id',
        'sucursal_origen_id',
        'stock_actual', // Este campo se puede calcular dinámicamente
        'motivo',
        'observaciones',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }
}