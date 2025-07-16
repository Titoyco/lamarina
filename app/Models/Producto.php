<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'descripcion',
        'observaciones',
        'categoria_id',
        'subcategoria_id',
        'proveedor_id',
        'stock',
        'punto_compra',
        'precio_compra',
        'iva',
        'descuento',
        'ganancia',
        'precio_venta',
        'Imagen'
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(ProductosCategoria::class, 'categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(ProductosSubcategoria::class, 'subcategoria_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}