<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProductosMovimiento;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'descripcion',
        'observaciones',
        'categoria_id',
        'subcategoria_id',
        'proveedor_id',
        'Imagen'
    ];

    // Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'categoria_id');
    }

    // Relación con subcategoría
    public function subcategoria()
    {
        return $this->belongsTo(ProductoSubcategoria::class, 'subcategoria_id');
    }

    // Relación con proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Relación muchos a muchos con sucursales (con campos adicionales)
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'productos_stock')
                    ->withPivot('stock', 'punto_compra', 'precio_venta')
                    ->withTimestamps();
    }

    // Relación con movimientos de productos
    public function movimientos()
    {
        return $this->hasMany(ProductoMovimiento::class, 'producto_id');
    }

    // Método para obtener el stock de una sucursal específica
    public function getStockSucursal($sucursal_id)
    {
        $pivotData = $this->sucursales()->where('sucursal_id', $sucursal_id)->first();
        return $pivotData ? $pivotData->pivot->stock : 0;
    }

    // Método para obtener el precio de venta de una sucursal específica
    public function getPrecioVentaSucursal($sucursal_id)
    {
        $pivotData = $this->sucursales()->where('sucursal_id', $sucursal_id)->first();
        return $pivotData ? $pivotData->pivot->precio_venta : 0;
    }

    // Método para obtener todos los stocks por sucursal
    public function getStockPorSucursales()
    {
        return $this->sucursales()->get()->mapWithKeys(function ($sucursal) {
            return [
                $sucursal->id => [
                    'sucursal' => $sucursal->nombre,
                    'stock' => $sucursal->pivot->stock,
                    'punto_compra' => $sucursal->pivot->punto_compra,
                    'precio_venta' => $sucursal->pivot->precio_venta
                ]
            ];
        });
    }

    // Método para obtener el stock total (suma de todas las sucursales)
    public function getStockTotal()
    {
        return $this->sucursales()->sum('productos_stock.stock');
    }
}