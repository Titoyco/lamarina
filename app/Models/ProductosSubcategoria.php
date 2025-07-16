<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosSubcategoria extends Model
{
    use HasFactory;

    protected $table = 'productos_subcategorias';

    protected $fillable = [
        'nombre'
    ];


    // Relación con Producto
    public function productos()
    {
        return $this->hasMany(Producto::class, 'subcategoria_id');
    }

}