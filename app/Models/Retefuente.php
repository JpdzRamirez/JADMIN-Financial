<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retefuente extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'retefuentes';

    public function venta()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function compra()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id1');
    }

}
