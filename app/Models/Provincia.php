<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Provincia extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'provincias';

    protected $fillable = ['nombre'];

    public function localidades(): HasMany
    {
        return $this->hasMany(Localidad::class, 'id_provincia');
    }
}
