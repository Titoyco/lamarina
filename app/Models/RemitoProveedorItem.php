<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemitoProveedorItem extends Model
{
    use HasFactory;

    protected $table = 'remitos_proveedores_items';

    protected $fillable = [
        'remito_id',
        'producto_id',
        'cantidad',
        'precio_compra',
        'precio_venta',
        'precio_venta_anterior',
        'stock_anterior',
        'stock_posterior',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_venta_anterior' => 'decimal:2',
        'stock_anterior' => 'decimal:2',
        'stock_posterior' => 'decimal:2',
    ];

    public function remito(): BelongsTo
    {
        return $this->belongsTo(RemitoProveedor::class, 'remito_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
