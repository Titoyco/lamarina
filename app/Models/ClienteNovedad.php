<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteNovedad extends Model
{
    protected $table = 'cliente_novedades';
    protected $fillable = ['id_cliente', 'user_id', 'usuario_nombre', 'mensaje', 'origen_accion'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}