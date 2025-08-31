<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCategoria extends Model
{
    use HasFactory;

    protected $table = 'productos_categorias';

    protected $fillable = [
        'nombre'
    ];



    // Relación con Producto
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

}