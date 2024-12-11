<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;
    protected $table = 'users';

    public function creditos()
    {
        return $this->hasMany(Credito::class, 'users_id');
    }

    public function referencias()
    {
        return $this->hasMany(Referencia::class, 'users_id');
    }

    public function placas()
    {
        return $this->hasMany(Placa::class, 'users_id');
    }

    public function tercero()
    {
        return $this->belongsTo(TerceroCahors::class, 'terceros_id');
    }

    public function personales()
    {
        return $this->hasOne(Personal::class, 'users_id');
    }
}
