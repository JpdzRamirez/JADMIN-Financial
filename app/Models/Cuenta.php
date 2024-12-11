<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'cuentas';
 
    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'cuentas_id');
    }

    public function padre()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'cuentas_id');
    }

}
