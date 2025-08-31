<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductoStock extends Pivot
{
    protected $table = 'productos_stock';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'stock',
        'punto_compra',
        'precio_venta',
    ];
    // Definir los tipos de datos para los campos numéricos
    // Esto es útil para evitar problemas de precisión con los números decimales
    // y para asegurarse de que se manejen correctamente en las consultas y operaciones.
    // Por ejemplo, si estás usando MySQL, puedes definirlos como double o decimal.
    // Aquí usamos double con 15 dígitos en total y 2 decimales
    // para los campos de stock, punto_compra y precio_venta.
    protected $casts = [
        'stock' => 'double',
        'punto_compra' => 'double',
        'precio_venta' => 'double',
    ];
    public $timestamps = true;

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Puedes agregar métodos adicionales si es necesario, por ejemplo, para calcular el stock total en una sucursal.
    public function scopeStockTotalPorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId)->sum('stock');
    }
}