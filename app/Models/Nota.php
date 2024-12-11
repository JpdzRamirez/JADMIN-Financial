<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'notas';

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'facturas_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'notas_id');
    }
}
