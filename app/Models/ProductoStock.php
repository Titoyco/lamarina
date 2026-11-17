<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoStock extends Model
{
    use HasFactory;

    protected $table = 'productos_stock';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'stock',
        'stock_minimo',
        'punto_compra',
        'precio_venta',
        'ubicacion',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'punto_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
