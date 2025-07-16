<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

        /**
     * Relación muchos a muchos con sucursales.
     */
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'user_sucursales', 'user_id', 'sucursal_id');
    }

    /**
     * Relación uno a muchos con rutas.
     */
    public function rutas()
    {
        return $this->hasMany(UsuarioRuta::class, 'user_id');
    }
}
