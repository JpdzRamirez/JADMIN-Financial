<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerceroJADMIN extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'terceros';

    public function usuario()
    {
        return $this->hasOne(User::class, 'terceros_id');
    }

    public function empresa()
    {
        return $this->hasOne(EmpresaJADMIN::class, 'terceros_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'terceros_id');
    }
}
