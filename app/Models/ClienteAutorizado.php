<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteAutorizado extends Model
{
    protected $table = 'cliente_autorizados';
    protected $guarded = [];

    /**
     * Cliente Titular al que pertenece la autorización.
     */
    public function titular(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_titular');
    }

    /**
     * Cliente Persona que actúa como autorizado/adicional.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_autorizado');
    }
}