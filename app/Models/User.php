<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username', 
        'password',
        'is_admin',
        'id_sucursal',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function provinciaId(): int
    {
        if ($this->sucursal && $this->sucursal->id_provincia) {
            return (int) $this->sucursal->id_provincia;
        }
        return in_array((int) $this->id_sucursal, [1, 2]) ? 2 : 1;
    }

    public function isTdf(): bool
    {
        return $this->provinciaId() === 2;
    }

    public function isChubut(): bool
    {
        return $this->provinciaId() === 1;
    }
}
