<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Localidad extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'localidades'; // Forzamos el plural en español

    protected $fillable = [
        'id_provincia',
        'nombre',
        'codigo_postal'
    ];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_localidad');
    }

    public function clientesFamiliar(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_localidad_familiar');
    }

    public function clientesTrabajo(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_localidad_trabajo');
    }
}
